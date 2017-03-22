using System.Collections.Specialized;
using System.IO;
using RestSharp;
using RestSharp.Deserializers;
using System.Collections.Generic;

namespace Nutria.CPE.Tools
{
    public class Configuration
    {

        public Configuration(NameValueCollection settings, string name)
        {
            var ds = Path.DirectorySeparatorChar;
            this.Name = name;
            this.RUC = this.Name.Split('-')[0];

            this.Workdir = string.Format(settings[Keys.Workdir], this.RUC);
            this.KSPath = this.Workdir + ds + Keys.IdentityFileName;

            var secrets = File.ReadAllText(this.Workdir + ds + Keys.SecretsFileName).Deserialize<Dictionary<string, string>>();
            this.KSPass = secrets[Keys.KSPass];
            this.SunatUser = secrets[Keys.SunatUser];
            this.SunatPass = secrets[Keys.SunatPass];

            this.UnsignedXmlPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".unsigned.xml";
            this.SignedXmlPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".request.xml";
            this.SignedDataPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".signature.json";
            this.SunatRequestZipPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".request.zip";
            this.SunatResponseZipPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".response.zip";
            this.SunatResponseXmlPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".response.xml";
            this.PdfPath = this.Workdir + ds + settings[Keys.FolderPdf] + ds + this.Name + ".pdf";
            this.ZipEntryName = this.Name + ".xml";

            this.DocumentURL = string.Format(settings[Keys.DocumentURL], this.Name);
            this.PdfURL = string.Format(settings[Keys.PdfURL], this.Name);
            this.PlatformApiURL = settings[Keys.PlatformApiURL];
        }

        

        public string Name { get; private set; }
        public string RUC { get; private set; }

        public string DocumentURL { get; private set; }
        public string PdfURL { get; private set; }
        public string PlatformApiURL { get; private set; }

        public string Workdir { get; private set; }
        public string KSPath { get; private set; }
        public string KSPass { get; private set; }

        public string SunatUser { get; private set; }
        public string SunatPass { get; private set; }

        public string UnsignedXmlPath { get; private set; }
        public string SignedXmlPath { get; private set; }
        public string SignedDataPath { get; private set; }
        public string SunatRequestZipPath { get; private set; }
        public string SunatResponseZipPath { get; private set; }
        public string SunatResponseXmlPath { get; private set; }
        public string PdfPath { get; private set; }
        public string ZipEntryName { get; private set; }

        
    }

    public static class StringExtension
    {
        public static T Deserialize<T>(this string str)
        {
            return new JsonDeserializer().Deserialize<T>(new RestResponse { Content = str });
        }
    }

    public class Keys
    {
        public const string DocumentURL = "template.document.url";
        public const string PdfURL      = "template.pdf.url";

        public const string PlatformApiURL = "platform.api.url";

        public const string Workdir = "workdir";

        public const string FolderXML = "folder.xml";
        public const string FolderPdf = "folder.pdf";

        public const string IdentityFileName = "identity.pfx";
        public const string SecretsFileName = "secrets.json";
        public const string KSPass = "keystore.pass";
        public const string SunatUser = "sunat.user";
        public const string SunatPass = "sunat.pass";

    }
}

