using System;
using System.IO;
using System.IO.Compression;
using System.Security.Cryptography.Xml;
using System.Xml;
using System.Collections.Generic;
using System.Web.Script.Serialization;
using RestSharp;

namespace Nutria.CPE.Tools.Security
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

        private IKeyManager keyManager;
        private XmlDocument document;
        private XmlElement signatureContainer;
        private SignedXml signedXml;
        private string signatureId;

        public Signer(XmlDocument document, IKeyManager keyManager, string signatureId = DefaultSignatureID)
        {
            this.document = document;
            this.keyManager = keyManager;
            this.signatureId = signatureId;
            this.signedXml = new SignedXml(this.document);
        }

        /// <summary>
        /// Configura el firmador.
        /// </summary>
        public void Configure()
        {
            //seteo de la llave de firma
            this.SetKeyElements();

            //agrega el contenedor para la firma digital al documento
            this.AddSignatureContainer();

            //configura los parametros del xmldsig
            this.ConfigureAttributes();
        }

        /// <summary>
        /// Agrega la firma digital. y recupera los datos de respuesta.
        /// </summary>
        public void AtachSignature()
        {
            //generacion de la firma digital
            this.signedXml.ComputeSignature();

            //agrega la firma digital al contenido
            this.signatureContainer.AppendChild(this.document.ImportNode(signedXml.GetXml(), true));

            //firma, hash y fecha
            this.DigestValue = this.document.GetElementsByTagName(DigestValueTag, Namespaces.Ds.URI)[0].InnerText;
            this.SignatureValue = this.document.GetElementsByTagName(SignatureValueTag, Namespaces.Ds.URI)[0].InnerText;
            this.Date = DateTime.Now;
        }

        /// <summary>
        /// Guarda el documento XML a una ruta en el disco.
        /// </summary>
        /// <param name="fileName">Nombre del archivo donde guardar el XML</param>
        public void Save(String fileName)
        {
            using (FileStream fs = new FileStream(fileName, FileMode.Create, FileAccess.Write))
            {
                this.Save(fs);
            }
        }

        /// <summary>
        /// Guarda el documento XML en un stream.
        /// </summary>
        /// <param name="target"></param>
        public void Save(Stream target)
        {
            document.Save(target);
        }

        /// <summary>
        /// Guarda el documento XML en un archivo ZIP. El XML es la unica entrada y es necesario especificar el nombre.
        /// </summary>
        /// <param name="zipFile"></param>
        /// <param name="entryName"></param>
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

        public Dictionary<string, string> GetResponse(string name)
        {
            var response = new Dictionary<string, string>();
            response["name"] = name;
            response["date"] = this.Date.ToString("yyyy-MM-dd HH:mm:ss");
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

		//TODO remover codigo legacy
        /*public void RelayResponse(string target, string name)
        {
            var client = new RestClient(target);
            var request = new RestRequest(Method.POST);
            request.RequestFormat = DataFormat.Json;
            request.AddBody(this.GetResponse(name));
            var response = client.Execute(request);
        }*/

        public void SaveJSONResponse(string name, string file)
        {
            using (StreamWriter sw = new StreamWriter(file, false))
            {
                sw.Write(GetJSONResponse(name));
            }
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

        public DateTime Date { get; private set; }

        public string DigestValue { get; private set; }

        public string SignatureValue { get; private set; }
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
