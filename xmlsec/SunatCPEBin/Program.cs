using System;
using System.IO;
using System.Configuration;
using System.Security.Cryptography.X509Certificates;
using Nutria.CPE.SunatClient;
using Nutria.CPE.SunatClient.BillService;
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

            }
            else if (args.Length == 1)
            {
                var conf = new Tools.Configuration(ConfigurationManager.AppSettings, args[0]);
                var type = args[0].Split('-')[1];
                var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
                var keyManager = new Tools.Security.PKCS12KeyManager(pk12);
                var client = new Tools.Platform.JSONRestClient(conf.PlatformApiURL);
                var sign = new Tools.SignProcess(conf, keyManager, client);
                var sunatzip = new Tools.SUNATResponse();
                sign.Execute();

                System.Net.ServicePointManager.UseNagleAlgorithm = true;
                System.Net.ServicePointManager.Expect100Continue = false;
                System.Net.ServicePointManager.CheckCertificateRevocationList = true;

                var sclient = InitClient(type);
                sclient.Endpoint.EndpointBehaviors.Add(new SecurityBehavior() { Username = conf.SunatUser, Password = conf.SunatPass });

                try
                {
                    if (type.In("RC", "RA"))
                    {
                        //TODO solo se puede enviar si es para facturas el resto sale rechazado
                        var name = conf.Name + ".zip";
                        var ticket = sclient.sendSummary(name, File.ReadAllBytes(conf.SunatRequestZipPath));
                        client.UpdateSunatResponse(args[0], DateTime.Now, "enviado", "Ticket recibido", sclient.Endpoint.Address.Uri.ToString(), ticket);
                    }
                    else
                    {
                        //descarga de la respuesta de sunat
                        //sclient.Open();
                        byte[] response = sclient.sendBill(conf.Name + ".zip", File.ReadAllBytes(conf.SunatRequestZipPath));
                        //sclient.Close();
                        File.WriteAllBytes(conf.SunatResponseZipPath, response);
                        sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
                        sunatzip.Load(conf.SunatResponseXmlPath);
                        //ENVIO DEL MENSAJE DE SUNAT
                        client.UpdateSunatResponse(args[0], DateTime.Now, "0".Equals(sunatzip.ResponseCode) ? "declarado" : "rechazado", sunatzip.Description, sclient.Endpoint.Address.Uri.ToString(), null);
                        //Descarga del PDF
                        var pdfclient = new System.Net.WebClient();
                        pdfclient.DownloadFile(conf.PdfURL, conf.PdfPath);
                    }
                }
                catch (System.ServiceModel.FaultException ex)
                {
                    //mensaje de error aplicativo
                    Console.WriteLine(ex.Message);
                    client.UpdateSunatResponse(args[0], DateTime.Now, "error", ex.Message, sclient.Endpoint.Address.Uri.ToString(), null);
                }
                catch (System.ServiceModel.CommunicationException ex)
                {
                    //error generico - se puede reintentar
                    Console.WriteLine(ex.Message);
                    client.UpdateSunatResponse(args[0], DateTime.Now, "error", ex.Message, sclient.Endpoint.Address.Uri.ToString(), null);
                }
            }
            else if ("ticket" == args[1])
            {
                var conf = new Tools.Configuration(ConfigurationManager.AppSettings, args[0]);
                var sclient = InitClient("01");
                var sunatzip = new Tools.SUNATResponse();
                var response = sclient.getStatus(args[2]);
                File.WriteAllBytes(conf.SunatResponseZipPath, response.content);
                sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
                Console.WriteLine(response.statusCode);
                return;
            }
        }

        public static billServiceClient InitClient(string documentType)
        {
            string configuration = null;
            if (documentType.In("01", "03", "07", "08", "RC", "RA"))
            {
                configuration = "facturas";
            }
            else if (documentType.In("20", "40"))
            {
                configuration = "certificados";
            }
            else if (documentType.In("09"))
            {
                configuration = "guias";
            }
            return new billServiceClient(configuration);
        }
    }

    static class Extensions
    {

        public static bool In<T>(this T item, params T[] items)
        {
            if (items == null)
            {
                throw new ArgumentNullException("items");
            }
            return items.Contains(item);
        }

    }
}



