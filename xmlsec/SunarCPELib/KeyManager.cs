using System.Security.Cryptography.Xml;
using System.Security.Cryptography.X509Certificates;
using System;

namespace org.nutria.sunat.xmldsig.lib
{
    public interface KeyManager
    {
        void SetKeyInfo(SignedXml signedXml);
    }

    public class PKCS12KeyManager : KeyManager
    {
        private X509Certificate2 certificate;

        public PKCS12KeyManager (X509Certificate2 certificate)
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
}
