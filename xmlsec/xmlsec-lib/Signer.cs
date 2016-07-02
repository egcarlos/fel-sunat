using System;
using System.IO;
using System.IO.Compression;
using System.Security.Cryptography.Xml;
using System.Xml;
using System.Collections.Generic;
using System.Web.Script.Serialization;
using RestSharp;

namespace org.nutria.sunat.xmldsig.lib
{

    public class Signer
    {
        const string DigestValueTag = "DigestValue";
        const string SignatureValueTag = "SignatureValue";
        const string ExtensionsTag = "UBLExtensions";
        const string ExtensionTag = "UBLExtension";
        const string ContentTag = "ExtensionContent";
        const string PrefixSeparator = ":";
        const string DefaultSignatureID = "IDSignKG";

        private KeyManager keyManager;
        private XmlDocument document;
        private XmlElement signatureContainer;
        private SignedXml signedXml;
        private string signatureId;

        public Signer(XmlDocument document, KeyManager keyManager, string signatureId = DefaultSignatureID)
        {
            this.document = document;
            this.keyManager = keyManager;
            this.signatureId = signatureId;
            this.signedXml = new SignedXml(this.document);
        }

        public void Save(String fileName)
        {
            using (FileStream fs = new FileStream(fileName, FileMode.Create, FileAccess.Write))
            {
                document.Save(fs);
            }
        }

        public void Save(Stream target)
        {
            document.Save(target);
        }

        public void SaveToZip(string zipFile, string entryName)
        {
            using (var zip = new FileStream(zipFile, FileMode.Create))
            {
                using (ZipArchive archive = new ZipArchive(zip, ZipArchiveMode.Create))
                {
                    var entry = archive.CreateEntry(entryName);
                    using (var eout = entry.Open())
                    {
                        this.Save(eout);
                    }
                }
            }
        }

        public void Configure()
        {
            //seteo de la llave de firma
            this.SetKeyElements();

            //agrega el contenedor para la firma digital al documento
            this.AddSignatureContainer();

            //configura los parametros del xmldsig
            this.ConfigureAttributes();
        }

        public Dictionary<string, string> GetResponse(string name)
        {
            var response = new Dictionary<string, string>();
            response["name"] = name;
            response["date"] = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            response["digestValue"] = this.DigestValue;
            response["signatureValue"] = this.SignatureValue;
            return response;
        }

        public String GetJSONResponse(string name)
        {
            var response = GetResponse(name);
            var serializer = new JavaScriptSerializer();
            var json = serializer.Serialize(response);
            return json;
        }
        
        public void RelayResponse(string target, string name)
        {
            var client = new RestClient(target);
            var request = new RestRequest("", Method.POST);
            request.AddParameter("name", name);
            request.AddParameter("digest", this.DigestValue);
            request.AddParameter("signature", this.SignatureValue);
            request.AddParameter("date", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss"));
            var response = client.Execute(request);
        }

        public void SaveJSONResponse(string name, string file)
        {
            using (StreamWriter sw = new StreamWriter(file, false))
            {
                sw.Write(GetJSONResponse(name));
            }
        }

        public void AtachSignature()
        {
            //generacion de la firma digital
            this.signedXml.ComputeSignature();

            //agrega la firma digital al contenido
            this.signatureContainer.AppendChild(this.document.ImportNode(signedXml.GetXml(),true));
        }

        public void SetKeyElements()
        {
            keyManager.SetKeyInfo(signedXml);
        }

        public void AddSignatureContainer()
        {
            var ext = Namespaces.Ext;
            var extensions = document.GetElementsByTagName(ExtensionsTag, ext.URI)[0];
            var extension = document.CreateElement(ext.Prefix + PrefixSeparator + ExtensionTag, ext.URI);
            extensions.AppendChild(extension);
            var content = document.CreateElement(ext.Prefix + PrefixSeparator + ContentTag, ext.URI);
            extension.AppendChild(content);
            signatureContainer = content;
        }

        public void ConfigureAttributes()
        {
            //configuracion de la firma
            signedXml.Signature.Id = this.signatureId;
            signedXml.SignedInfo.CanonicalizationMethod = SignedXml.XmlDsigCanonicalizationWithCommentsUrl;
            signedXml.SignedInfo.SignatureMethod = SignedXml.XmlDsigRSASHA1Url;
            var reference = new Reference(String.Empty);
            reference.AddTransform(new XmlDsigEnvelopedSignatureTransform());
            reference.DigestMethod = SignedXml.XmlDsigSHA1Url;
            signedXml.SignedInfo.AddReference(reference);
        }

        public string DigestValue
        {
            get
            {
                return this.document.GetElementsByTagName(DigestValueTag, Namespaces.Ds.URI)[0].InnerText;
            }
        }

        public string SignatureValue
        {
            get
            {
                return this.document.GetElementsByTagName(SignatureValueTag, Namespaces.Ds.URI)[0].InnerText;
            }
        }
    }

    public class Namespaces
    {
        private static Namespace ext = new Namespace("ext", "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
        private static Namespace ds = new Namespace("ds", "http://www.w3.org/2000/09/xmldsig#");

        public static Namespace Ext
        {
            get
            {
                return Namespaces.ext;
            }
        }

        public static Namespace Ds
        {
            get
            {
                return Namespaces.ds;
            }
        }
    }

    public struct Namespace
    {
        public string Prefix;
        public string URI;

        public Namespace(string Prefix, string URI)
        {
            this.Prefix = Prefix;
            this.URI = URI;
        }
    }

}
