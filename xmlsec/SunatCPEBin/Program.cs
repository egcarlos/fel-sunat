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
                Console.WriteLine("uso: programa <ambiente> <comando> <id> [<argumentos>]");
                return;
            }

            var enviroment = args[0];
            var command = args[1];
            var id = args[2];
            switch (command)
            {
                case "query":
                    Query(enviroment, id);
                    break;
                case "ticket":
                    var ticket = args[3];
                    Ticket(enviroment, id, ticket);
                    break;
                case "declare":
                    var type = id.Split('-')[1];
                    if (type.In("RR", "RC", "RA"))
                    {
                        DeclareSummary(enviroment, id);
                    }
                    else
                    {
                        DeclareDocument(enviroment, id);
                    }
                    break;
            }

            /*if ("sendzip" == args[1])
            {
                Console.WriteLine(DateTime.Now);
                Console.WriteLine("Declarando Documento");
                byte[] response = sclient.sendBill(conf.Name + ".zip", File.ReadAllBytes(conf.Name + ".zip"));
                //sclient.Close();
                File.WriteAllBytes(conf.SunatResponseZipPath, response);
                sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
                sunatzip.Load(conf.SunatResponseXmlPath);
                //ENVIO DEL MENSAJE DE SUNAT
                Console.WriteLine(DateTime.Now);
                Console.WriteLine(sunatzip.Description);
            }*/
        }

        private static void DeclareDocument(string enviroment, string id)
        {
            //prepare
            var type = id.Split('-')[1];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var sclient = new ClientManager(enviroment, type, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            var name = conf.Name + ".zip";
            var client = new Tools.Platform.JSONRestClient(conf.PlatformApiURL);
            var sunatzip = new Tools.SUNATResponse();
            //signature
            var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
            var keyManager = new Tools.Security.PKCS12KeyManager(pk12);
            var sign = new Tools.SignProcess(conf, keyManager, client);
            sign.Execute();

            //operation
            Response("Declarando Documento", "Endpoint: " + sclient.Endpoint.Address.Uri, "");
            byte[] response = sclient.sendBill(conf.Name + ".zip", File.ReadAllBytes(conf.SunatRequestZipPath));
            //sclient.Close();
            File.WriteAllBytes(conf.SunatResponseZipPath, response);
            sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
            sunatzip.Load(conf.SunatResponseXmlPath);
            //response & update platform
            try
            {
                Response("RESPUESTA " + sunatzip.Description);
                client.UpdateSunatResponse(id, DateTime.Now, "0".Equals(sunatzip.ResponseCode) ? "declarado" : "rechazado", sunatzip.Description, sclient.Endpoint.Address.Uri.ToString(), null);
                //pdf
                var pdfclient = new System.Net.WebClient();
                pdfclient.DownloadFile(conf.PdfURL, conf.PdfPath);
            }
            catch (System.ServiceModel.FaultException ex)
            {
                var r = ex.Code.Name  + "-" + ex.Code.SubCode;
                Response(r);
                client.UpdateSunatResponse(id, DateTime.Now, "error", r, sclient.Endpoint.Address.Uri.ToString(), null);
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
                var r = msg;
                Response(r);
                client.UpdateSunatResponse(id, DateTime.Now, "error", r, sclient.Endpoint.Address.Uri.ToString(), null);
            }
        }

        private static void DeclareSummary(string enviroment, string id)
        {
            //prepare
            var type = id.Split('-')[1];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var sclient = new ClientManager(enviroment, type, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            var name = conf.Name + ".zip";
            var client = new Tools.Platform.JSONRestClient(conf.PlatformApiURL);
            //signature
            var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
            var keyManager = new Tools.Security.PKCS12KeyManager(pk12);
            var sign = new Tools.SignProcess(conf, keyManager, client);
            sign.Execute();

            try
            {
                //operation
                Response("Declarando Resumen", "Endpoint: " + sclient.Endpoint.Address.Uri, "");
                var ticket = sclient.sendSummary(name, File.ReadAllBytes(conf.SunatRequestZipPath));
                client.UpdateSunatResponse(id, DateTime.Now, "enviado", "Ticket recibido", sclient.Endpoint.Address.Uri.ToString(), ticket);
                //response
                Response("TICKET " + ticket);
            }
            catch (System.ServiceModel.FaultException ex)
            {
                var response = ex.Code.Name;
                Console.WriteLine(DateTime.Now);
                Console.WriteLine(response);
                client.UpdateSunatResponse(id, DateTime.Now, "error", response, sclient.Endpoint.Address.Uri.ToString(), null);
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
                client.UpdateSunatResponse(id, DateTime.Now, "error", response, sclient.Endpoint.Address.Uri.ToString(), null);
            }
        }

        private static void Ticket(string enviroment, string id, string ticket)
        {
            //prepare
            var type = id.Split('-')[1];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var sclient = new ClientManager(enviroment, type, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            var sunatzip = new Tools.SUNATResponse();
            //operation
            sclient.Open();
            var response = sclient.getStatus(ticket);
            sclient.Close();
            File.WriteAllBytes(conf.SunatResponseZipPath, response.content);
            sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
            //response
            Response(response.statusCode);
            //TODO agregar el update del sistema con
            //client.updateSunatResponse.......
        }

        private static void Query(string enviroment, string id)
        {
            //prepare
            var type = id.Split('-')[1];
            var serial = id.Split('-')[2];
            var number = id.Split('-')[3];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var qclient = new SunatClient.SunatQuery.ClientManager(enviroment, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            //operation
            var response = qclient.getStatus(conf.RUC, type, serial, int.Parse(number));
            //response
            Response(response.statusCode, response.statusMessage);
        }

        private static void Response(params string[] args)
        {
            Console.WriteLine(DateTime.Now);
            foreach (var arg in args)
            {
                Console.WriteLine(arg);
            }
        }
    }
}



