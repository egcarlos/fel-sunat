using System;
using System.Configuration;
using System.Collections.Specialized;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

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
            this.KSPath = this.Workdir + ds + settings[this.RUC + Keys.KSPathSuffix];
            this.KSPass = settings[this.RUC + Keys.KSPassSuffix];

            this.UnsignedXmlPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".unsigned.xml";
            this.SignedXmlPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".signed.xml";
            this.SignedDataPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".signature.json";
            this.SunatZipPath = this.Workdir + ds + settings[Keys.FolderXML] + ds + this.Name + ".sunat.zip";
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

        public string UnsignedXmlPath { get; private set; }
        public string SignedXmlPath { get; private set; }
        public string SignedDataPath { get; private set; }
        public string SunatZipPath { get; private set; }
        public string PdfPath { get; private set; }
        public string ZipEntryName { get; private set; }

        
    }

    public class Keys
    {
        public const string DocumentURL = "template.document.url";
        public const string PdfURL      = "template.pdf.url";

        public const string PlatformApiURL = "platform.api.url";

        public const string Workdir = "workdir";

        public const string FolderXML = "folder.xml";
        public const string FolderPdf = "folder.pdf";

        public const string KSPathSuffix = ".keystore.name";
        public const string KSPassSuffix = ".keystore.pass";

        
    }
}

