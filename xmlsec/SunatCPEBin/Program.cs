using System;
using System.IO;
using System.Configuration;
using System.Security.Cryptography.X509Certificates;
using System.Xml;
using RestSharp;
using Nutria.CPE.SunatClient;
using Nutria.CPE.SunatClient.BillService;

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
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, args[0]);
            var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
            var keyManager = new Tools.Security.PKCS12KeyManager(pk12);
            var client = new Tools.Platform.JSONRestClient(conf.PlatformApiURL);
            var sign = new Tools.SignProcess(conf, keyManager, client);
            var sunatzip = new Tools.SUNATResponse();
            sign.Execute();

            //descarga de la respuesta de sunat
            var sclient = new billServiceClient("BillServicePort");
            sclient.Endpoint.EndpointBehaviors.Add(new SecurityBehavior() { Username = "20100318696MODDATOS", Password = "moddatos" });
            byte[] response = sclient.sendBill(conf.Name + ".zip", File.ReadAllBytes(conf.SunatRequestZipPath));
            File.WriteAllBytes(conf.SunatResponseZipPath, response);

            //TODO leer el archivo descargado
            sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
            sunatzip.Load(conf.SunatResponseXmlPath);
            
            //ENVIO DEL MENSAJE DE SUNAT
            client.UpdateSunatResponse(args[0], DateTime.Now, "0".Equals(sunatzip.ResponseCode)?"declarado":"rechazado", sunatzip.Description);

            //Descarga del PDF
            var pdfclient = new System.Net.WebClient();
            pdfclient.DownloadFile(conf.PdfURL, conf.PdfPath);

        }
    }
}



