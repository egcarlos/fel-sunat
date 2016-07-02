﻿using System;
using System.Configuration;
using System.Collections.Specialized;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace org.nutria.sunat.xmldsig.lib
{
    public class Configuration
    {

        public Configuration(NameValueCollection settings, string[] args)
        {
            var ds = Path.DirectorySeparatorChar;
            this.Name = args[0];
            this.RUC = this.Name.Split('-')[0];

            this.Workdir = string.Format(settings[Keys.Workdir], this.RUC);
            this.KSPath = this.Workdir + ds + settings[this.RUC + Keys.KSPathSuffix];
            this.KSPass = settings[this.RUC + Keys.KSPassSuffix];

            this.UnsignedXmlPath = this.Workdir + ds + Keys.FilesFolderName + ds + this.Name + ".unsigned.xml";
            this.SignedXmlPath = this.Workdir + ds + Keys.FilesFolderName + ds + this.Name + ".signed.xml";
            this.SignedDataPath = this.Workdir + ds + Keys.FilesFolderName + ds + this.Name + ".signature.json";
            this.SunatZipPath = this.Workdir + ds + Keys.FilesFolderName + ds + this.Name + ".sunat.zip";
            this.ZipEntryName = this.Name + ".xml";

            this.DocumentURL = string.Format(settings[Keys.DocumentURL], this.Name);
            this.PdfURL = string.Format(settings[Keys.PdfURL], this.Name);
            this.UpdateSignatureURL = settings[Keys.UpdateSignatureURL];
        }

        public string Name { get; private set; }
        public string RUC { get; private set; }

        public string DocumentURL { get; private set; }
        public string PdfURL { get; private set; }
        public string UpdateSignatureURL { get; private set; }

        public string Workdir { get; private set; }
        public string KSPath { get; private set; }
        public string KSPass { get; private set; }

        public string UnsignedXmlPath { get; private set; }
        public string SignedXmlPath { get; private set; }
        public string SignedDataPath { get; private set; }
        public string SunatZipPath { get; private set; }
        public string ZipEntryName { get; private set; }
    }

    public class Keys
    {

        public const string DocumentURL = "template.document_url";
        public const string PdfURL = "template.pdf_url";
        public const string UpdateSignatureURL = "update.signature_url";
        public const string Workdir = "workdir";
        public const string KSPathSuffix = ".keystore.name";
        public const string KSPassSuffix = ".keystore.pass";
        public const string FilesFolderName = "documentos";
    }
}
