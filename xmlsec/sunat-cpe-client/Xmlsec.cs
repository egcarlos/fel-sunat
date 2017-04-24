using System;
using System.Collections.Generic;
using System.Xml;
using System.Security.Cryptography.Xml;
using System.Security.Cryptography.X509Certificates;

namespace CPE.Client.Xmlsec
{
    public interface IKeyManager
    {
        void SetKeyInfo(SignedXml signedXml);
    }

    public class PKCS12KeyManager : IKeyManager
    {
        private X509Certificate2 certificate;

        public PKCS12KeyManager(String X509CertificatePath, String X509CertificatePass)
        {
            certificate = new X509Certificate2(X509CertificatePath, X509CertificatePass);
        }

        public PKCS12KeyManager(X509Certificate2 certificate)
        {
            this.certificate = certificate;
        }

        public void SetKeyInfo(SignedXml signedXml)
        {
            signedXml.SigningKey = certificate.PrivateKey;
            signedXml.KeyInfo = new KeyInfo();
            signedXml.KeyInfo.AddClause(new KeyInfoX509Data(certificate));
        }
    }

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
