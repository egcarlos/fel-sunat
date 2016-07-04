using RestSharp;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Xml;

namespace org.nutria.sunat.xmldsig.lib
{
    public class SignProcess
    {
        public Configuration Configuration { get; private set; }
        public KeyManager KeyManager { get; private set; }

        public string DigestValue { get; private set; }
        public string SignatureValue { get; private set; }
        public string JSONResponse { get; private set; }

        public bool SaveUnsignedDocument { get; set; }
        public bool SaveSignedDocument { get; set; }
        public bool SaveSunatZIP { get; set; }
        public bool SaveJSONResponse { get; set; }
        public bool RelayJSONResponse { get; set; }


        public SignProcess(Configuration configuration, KeyManager keyManager)
        {
            this.Configuration = configuration;
            this.KeyManager = keyManager;
            this.SaveUnsignedDocument = true;
            this.SaveSignedDocument = true;
            this.SaveSunatZIP = true;
            this.SaveJSONResponse = true;
            this.RelayJSONResponse = true;
        }

        public void Execute()
        {
            //carga del documento XML
            var doc = new XmlDocument();
            doc.PreserveWhitespace = true;
            doc.Load(this.Configuration.DocumentURL);

            //firma
            Signer signer = new Signer(doc, this.KeyManager);
            signer.Configure();
            //grabado condicional del archivo intermedio
            if (this.SaveUnsignedDocument) signer.Save(this.Configuration.UnsignedXmlPath);
            signer.AtachSignature();

            //grabado condicional de resultados
            if (this.SaveUnsignedDocument) signer.Save(this.Configuration.SignedXmlPath);
            if (this.SaveSunatZIP) signer.SaveToZip(this.Configuration.SunatZipPath, this.Configuration.ZipEntryName);
            if (this.SaveJSONResponse) signer.SaveJSONResponse(this.Configuration.Name, this.Configuration.SignedDataPath);

            //envio de la respuesta al servidor
            if (this.RelayJSONResponse) signer.RelayResponse(this.Configuration.UpdateSignatureURL, this.Configuration.Name);

            //carga de las respuestas
            this.DigestValue = signer.DigestValue;
            this.SignatureValue = signer.SignatureValue;
            this.JSONResponse = signer.GetJSONResponse(this.Configuration.Name);
        }
    }
}
