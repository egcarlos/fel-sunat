using RestSharp;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Xml;

namespace Nutria.CPE.Tools
{
    public class SignProcess
    {
        public Configuration Configuration { get; private set; }
        public Security.IKeyManager KeyManager { get; private set; }
        public Platform.IPlatformClient Client { get; private set; }

        public string DigestValue { get; private set; }
        public string SignatureValue { get; private set; }
        public Dictionary<string, string> SignatureResponse { get; private set; }

        public bool SaveUnsignedDocument { get; set; }
        public bool SaveSignedDocument { get; set; }
        public bool SaveSunatZIP { get; set; }
        public bool SaveJSONResponse { get; set; }
        public bool RelaySignature { get; set; }
        public bool RelaySunatResponse { get; set; }


        public SignProcess(Configuration configuration, Security.IKeyManager keyManager, Platform.IPlatformClient client)
        {
            this.Configuration = configuration;
            this.KeyManager = keyManager;
            this.Client = client;
            this.SaveUnsignedDocument = true;
            this.SaveSignedDocument = true;
            this.SaveSunatZIP = true;
            this.SaveJSONResponse = true;
            this.RelaySignature = true;
            this.RelaySunatResponse = true;
        }

        public void Execute()
        {
            //carga del documento XML
            var doc = new XmlDocument();
            doc.PreserveWhitespace = true;
            doc.Load(this.Configuration.DocumentURL);

            //firma
            Security.Signer signer = new Security.Signer(doc, this.KeyManager);
            signer.Configure();
            //grabado condicional del archivo intermedio
            if (this.SaveUnsignedDocument) signer.Save(this.Configuration.UnsignedXmlPath);
            signer.AtachSignature();

            //grabado condicional de resultados
            if (this.SaveUnsignedDocument) signer.Save(this.Configuration.SignedXmlPath);
            if (this.SaveSunatZIP) signer.SaveToZip(this.Configuration.SunatRequestZipPath, this.Configuration.ZipEntryName);
            if (this.SaveJSONResponse) signer.SaveJSONResponse(this.Configuration.Name, this.Configuration.SignedDataPath);

            //envio de la respuesta al servidor
            if (this.RelaySignature) this.Client.UpdateSignature(Configuration.Name, signer.Date, signer.SignatureValue, signer.DigestValue);

            //carga de las respuestas
            this.DigestValue = signer.DigestValue;
            this.SignatureValue = signer.SignatureValue;
            this.SignatureResponse = signer.GetResponse(Configuration.Name);

            
        }
    }
}

