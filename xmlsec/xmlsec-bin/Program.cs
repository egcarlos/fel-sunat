using System;
using System.Collections.Generic;
using System.Configuration;
using System.Security.Cryptography.X509Certificates;
using System.Web.Script.Serialization;
using System.Xml;
using org.nutria.sunat.xmldsig.lib;

namespace org.nutria.sunat.xmldsig.bin
{
    class Program
    {
        static string PatchTemplate(string key, string ruc, string tipo, string numero)
        {
            string text = ConfigurationManager.AppSettings.Get(key);
            text = text.Replace("{ruc}", ruc);
            text = text.Replace("{tipo}", tipo);
            text = text.Replace("{numero}", numero);
            return text;
        }
        
        static void Main(string[] args)
        {
            //parametros de operacion del comando
            string ruc = args[0], tipo = args[1], numero = args[2];
            string url = PatchTemplate("document.url.template", ruc, tipo, numero);
            string name = PatchTemplate("document.name.template", ruc, tipo, numero);
            string workdir = ConfigurationManager.AppSettings.Get(ruc + ".workdir");
            string certPath = workdir + "\\identity.pfx";
            string certPass = ConfigurationManager.AppSettings.Get(ruc + ".keystore.pass");

            //cargar el certificado digital
            var pk12 = new X509Certificate2(certPath, certPass);
            KeyManager keyManager = new PKCS12KeyManager(pk12);

            //cargar el xml
            var doc = new XmlDocument();
            doc.PreserveWhitespace = true;
            doc.Load(url);
            
            //crear el firmador
            Signer signer = new Signer(doc, keyManager);
            signer.Configure();
            //guarda archivo sin firmar
            signer.Save(workdir + "\\documents\\" + name + ".unsigned.xml");
            //firma el archivo
            signer.AtachSignature();
            //guarda archivo firmado
            signer.Save(workdir + "\\documents\\" + name + ".xml");
            //genera el zip
            signer.SaveToZip(workdir + "\\documents\\" + name + ".zip", name + ".xml");

            
            var response = new Dictionary<string, string>();
            response["name"] = name;
            response["date"] = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            response["signatureValue"] = name;
            response["digestValue"] = name;
            var serializer = new JavaScriptSerializer();
            var json = serializer.Serialize(response);

            Console.WriteLine(json);


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


