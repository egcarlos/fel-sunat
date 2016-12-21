using System;
using System.IO;
using System.Configuration;
using System.Security.Cryptography.X509Certificates;
using SunatClient.Sunat;
using System.Linq;

namespace Nutria.CPE.Bin
{
    class Program
    {
        static string PatchTemplate(string key, params object[] args)
        {
            string template = ConfigurationManager.AppSettings.Get(key);
            return string.Format(template, args);
        }

        static void Main(string[] args)
        {
            if (args.Length == 0)
            {
                Console.WriteLine("Invalid Usage");
                return;
            }
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, args[0]);
            //move to configuration
            var type = args[0].Split('-')[1];
            var serial = args[0].Split('-')[2];
            var number = args[0].Split('-')[3];
            var sclient = new ClientManager("testing", type, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;

            if (args.Length == 1)
            {
                var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
                var keyManager = new Tools.Security.PKCS12KeyManager(pk12);
                var client = new Tools.Platform.JSONRestClient(conf.PlatformApiURL);
                var sign = new Tools.SignProcess(conf, keyManager, client);
                var sunatzip = new Tools.SUNATResponse();
                sign.Execute();

                try
                {
                    if (type.In("RC", "RA"))
                    {
                        Console.WriteLine(DateTime.Now);
                        Console.WriteLine("Declarando Documento");
                        //TODO solo se puede enviar si es para facturas el resto sale rechazado
                        var name = conf.Name + ".zip";
                        var ticket = sclient.sendSummary(name, File.ReadAllBytes(conf.SunatRequestZipPath));
                        Console.WriteLine(DateTime.Now);
                        Console.WriteLine("ticket de consultas: " + ticket);
                        client.UpdateSunatResponse(args[0], DateTime.Now, "enviado", "Ticket recibido", sclient.Endpoint.Address.Uri.ToString(), ticket);
                    }
                    else
                    {
                        //descarga de la respuesta de sunat
                        //sclient.Open();
                        Console.WriteLine(DateTime.Now);
                        Console.WriteLine("Declarando Documento");
                        byte[] response = sclient.sendBill(conf.Name + ".zip", File.ReadAllBytes(conf.SunatRequestZipPath));
                        //sclient.Close();
                        File.WriteAllBytes(conf.SunatResponseZipPath, response);
                        sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
                        sunatzip.Load(conf.SunatResponseXmlPath);
                        //ENVIO DEL MENSAJE DE SUNAT
                        Console.WriteLine(DateTime.Now);
                        Console.WriteLine(sunatzip.Description);
                        client.UpdateSunatResponse(args[0], DateTime.Now, "0".Equals(sunatzip.ResponseCode) ? "declarado" : "rechazado", sunatzip.Description, sclient.Endpoint.Address.Uri.ToString(), null);
                        //Descarga del PDF
                        var pdfclient = new System.Net.WebClient();
                        pdfclient.DownloadFile(conf.PdfURL, conf.PdfPath);
                    }
                }
                catch (System.ServiceModel.FaultException ex)
                {
                    var response = ex.Code.Name;
                    Console.WriteLine(DateTime.Now);
                    Console.WriteLine(response);
                    client.UpdateSunatResponse(args[0], DateTime.Now, "error", response, sclient.Endpoint.Address.Uri.ToString(), null);
                }
                catch (Exception ex)
                {
                    string msg;
                    msg = ex.InnerException != null ? string.Concat(ex.InnerException.Message, ex.Message) : ex.Message;
                    var faultCode = "<faultcode>";
                    if (msg.Contains(faultCode))
                    {
                        var posicion = msg.IndexOf(faultCode, StringComparison.Ordinal);
                        var codigoError = msg.Substring(posicion + faultCode.Length, 4);
                        msg = $"El Código de Error es {codigoError}";
                    }
                    var response = msg;
                    Console.WriteLine(DateTime.Now);
                    Console.WriteLine(response);
                    client.UpdateSunatResponse(args[0], DateTime.Now, "error", response, sclient.Endpoint.Address.Uri.ToString(), null);
                }
            }
            else if ("query" == args[1])
            {
                var qclient = new SunatClient.SunatQuery.ClientManager("live", conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
                var response = qclient.getStatus(conf.RUC, type, serial, int.Parse(number));
                Console.WriteLine(DateTime.Now);
                Console.WriteLine(response.statusCode);
                Console.WriteLine(response.statusMessage);
            }
            else if ("ticket" == args[1])
            {
                var sunatzip = new Tools.SUNATResponse();
                sclient.Open();
                var response = sclient.getStatus(args[2]);
                sclient.Close();
                File.WriteAllBytes(conf.SunatResponseZipPath, response.content);
                sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
                Console.WriteLine(DateTime.Now);
                Console.WriteLine(response.statusCode);
                return;
            }
        }


    }
}



