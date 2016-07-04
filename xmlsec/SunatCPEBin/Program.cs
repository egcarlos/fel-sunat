using System;
using System.Configuration;
using System.Security.Cryptography.X509Certificates;
using System.Xml;
using org.nutria.sunat.xmldsig.lib;
using RestSharp;

namespace org.nutria.sunat.xmldsig.bin
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
            var conf = new lib.Configuration(ConfigurationManager.AppSettings, args);
            //cargar el certificado digital
            var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
            KeyManager keyManager = new PKCS12KeyManager(pk12);

            var sign = new SignProcess(conf, keyManager);

            sign.Execute();

            //Console.WriteLine(sign.JSONResponse);

            var client = new RestClient(conf.UpdateSignatureURL);
            var request = new RestRequest(Method.POST);
            request.RequestFormat = DataFormat.Json;
            request.AddParameter("application/json; charset=utf-8", sign.JSONResponse, ParameterType.RequestBody);
            var response = client.Execute(request);

            //Console.ReadLine();
            /*
            //validar el documento recien firmado
            var document = new XmlDocument();

            document.PreserveWhitespace = true;
            document.Load(workdir + "\\documents\\" + name + ".xml");
            var signedXml = new SignedXml(document);
            XmlNodeList nodeList = document.GetElementsByTagName("Signature", "http://www.w3.org/2000/09/xmldsig#");
            signedXml.LoadXml((XmlElement)nodeList[0]);

            var valid = signedXml.CheckSignature();
            System.Console.WriteLine(valid);
            System.Console.ReadLine();
            */
        }
    }
}


