using System;
using System.Collections.Generic;
using System.Xml;
using CommandLine;
using System.ServiceModel;
using System.IO;
using CPE.Client.Xmlsec;

namespace CPE.Bin
{
    class Program
    {
        public Options Options { get; set; }
        public bool Valid { get; set; }
        public CPE.Platform.Client Platform { get; set; }
        public List<CPE.Platform.Settings> Settings { get; set; }
        public CPE.Platform.Settings CurrentSettings { get; set; }

        static void Main(string[] args)
        {
            var program = new Program() { Options = new Options() };
            program.Valid = Parser.Default.ParseArguments(args, program.Options);

            if (program.Valid)
            {
                program.MainLoop();

            }
        }

        public void MainLoop()
        {
            Prepare();
            switch (Options.Action)
            {
                case Options.Configuration:
                    Configuration();
                    break;
                case Options.Query:
                    Query();
                    break;
                case Options.Declare:
                    Declare();
                    break;
                case Options.Sign:
                    Sign();
                    break;
                case Options.Print:
                    Print();
                    break;
                case Options.Relay:
                    Relay();
                    break;
            }
        }

        void Prepare()
        {
            Platform = new CPE.Platform.Client() { BaseUrl = Options.Platform };
            //set the issuer and workdir based on the document if present
            if (Options.Document != null)
            {
                var ruc = Options.Document.Split('-')[0];
                Options.Issuer = "6-" + ruc;
                Options.Workdir = Path.Combine(Options.Workdir, ruc);
            }
            //read platform settings and determine if there is a single setting to use for the request
            Settings = Platform.Settings(Options.Issuer, Options.Environment);
            if (Options.Issuer != null && Options.Environment != null && Settings.Count == 1)
            {
                CurrentSettings = Settings[0];
            }
        }

        void Configuration()
        {
            foreach (CPE.Platform.Settings setting in Settings)
            {
                Console.WriteLine(setting);
            }
        }

        void Query()
        {
            Log("Using settings", CurrentSettings);
            var type = Options.Document.Split('-')[1];
            if (type.IsSummary())
            {
                var endpoint = type.AsDeclareTarget(CurrentSettings);
                var manager = new CPE.Client.DeclareClientManager(endpoint, CurrentSettings.SunatUser, CurrentSettings.SunatPass);
                try
                {
                    var data = manager.QueryTicket(Options.TicketNumber);
                    HandleCompressedCDR(data);
                }
                finally
                {
                    Log("Request:", manager.Behaviors.Messages.Request);
                    Log("Reply:", manager.Behaviors.Messages.Reply);
                }
            }
            else
            {
                var manager = new CPE.Client.QueryClientManager(CurrentSettings.QueryPath, CurrentSettings.SunatUser, CurrentSettings.SunatPass);
                try
                {
                    var response = manager.GetCDR(Options.Document);
                    Log("Status Code: " + response.statusCode);
                    var ccdr = response.content;
                    HandleCompressedCDR(ccdr);
                }
                catch (FaultException ex)
                {
                    Platform.UpdateError(Options.Environment, Options.Document, ex.Code.Name, ex.Message);
                }
                finally
                {
                    Log("Request:", manager.Behaviors.Messages.Request);
                    Log("Reply:", manager.Behaviors.Messages.Reply);
                }
            }
        }

        void Relay()
        {
            Log("Using settings", CurrentSettings);
            var requestFile = LoadRequestFile();
            SendFileToSunat(requestFile);
        }

        void Declare()
        {
            Log("Using settings", CurrentSettings);
            var requestFile = GetRequestFile();
            SendFileToSunat(requestFile);
        }

        void SendFileToSunat(byte[] requestFile)
        {
            var type = Options.Document.Split('-')[1];
            string endpoint = type.AsDeclareTarget(CurrentSettings);
            var manager = new CPE.Client.DeclareClientManager(endpoint, CurrentSettings.SunatUser, CurrentSettings.SunatPass);
            try
            {
                var name = FileNamePart();
                if (type.IsSummary())
                {
                    var ticket = manager.DeclareSummary(name, requestFile);
                    HandleTicket(ticket);
                }
                else
                {
                    byte[] ccdr = manager.Declare(name, requestFile);
                    HandleCompressedCDR(ccdr);
                }
            }
            catch (FaultException ex)
            {
                var document = new XmlDocument();
                document.LoadXml(manager.Behaviors.Messages.Reply);
                var message = document.GetElementsByTagName("Fault", "http://schemas.xmlsoap.org/soap/envelope/")[0].InnerXml;
                Platform.UpdateError(Options.Environment, Options.Document, ex.Code.Name, message);
            }
            finally
            {
                Log("Request:", manager.Behaviors.Messages.Request);
                Log("Reply:", manager.Behaviors.Messages.Reply);
            }
        }

        void Sign()
        {
            Log("Using settings", CurrentSettings);
            string endpoint = Options.Document.Split('-')[1].AsDeclareTarget(CurrentSettings);
            GetRequestFile();
        }

        void Print ()
        {
            Log("Using settings", CurrentSettings);
            //descargar las facturas en PDF
            var pdf_bytes = Platform.GetPDF(Options.Environment, Options.Document);
            var file = PersistPDF(Options.Document + ".pdf", pdf_bytes);
        }

        /// <summary>
        /// Carga el archivo firmado desde la ruta de trabajo del disco duro.
        /// </summary>
        /// <returns></returns>
        byte[] LoadRequestFile()
        {
            return LoadFile(Options.Document + ".request.zip");
        }

        byte[] GetRequestFile()
        {
            //recover plain document from platform
            var document = Platform.GetPlainDocument(Options.Environment, Options.Document);
            //TODO document fixed setting for file name in working directory
            var key = new PKCS12KeyManager(Path.Combine(Options.Workdir,"identity.pfx"), CurrentSettings.KeyStorePass);
            //atach signature to document
            //TODO refactor since is no longer necesary to have a fragmented process 
            var signer = new Signer(document, key);
            signer.Configure();
            signer.AtachSignature();
            //replicate signature and hash to portal
            Platform.UpdateSignature(Options.Environment, Options.Document, signer.DigestValue, signer.SignatureValue);
            var signedFile = document.GetBytes();
            var requestFile = document.ZipRequest(FileNamePart() + ".xml");
            //save zipped file to disk
            PersistFile(Options.Document + ".request.xml", signedFile);
            PersistFile(Options.Document + ".request.zip", requestFile);
            return requestFile;
        }

        void HandleCompressedCDR(byte[] ccdr)
        {
            PersistFile(Options.Document + ".response.zip", ccdr);
            byte[] cdr = ccdr.UnzipResponse();
            var document = new XmlDocument();
            document.Load(cdr.AsStream());
            var responseCode = document.GetElementsByTagName("ResponseCode", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2")[0].InnerText;
            var description = document.GetElementsByTagName("Description", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2")[0].InnerText;
            Platform.UpdateCDRResponse(CurrentSettings.Enviroment, Options.Document, responseCode, description);
            Log("Response Code: " + responseCode, "Description: " + description);
        }

        void HandleTicket (string ticket)
        {
            Platform.UpdateTicket(CurrentSettings.Enviroment, Options.Document, ticket);
        }

        byte[] Compress(byte[] data, string entry)
        {
            return null;
        }

        void PersistFile(string name, byte[] data)
        {
            File.WriteAllBytes(Path.Combine(Options.Workdir, Options.Environment, "xml", name), data);
        }

        byte[] LoadFile(string name)
        {
            return File.ReadAllBytes(Path.Combine(Options.Workdir, Options.Environment, "xml", name));
        }

        string PersistPDF(string name, byte[] data)
        {
            var file = Path.Combine(Options.Workdir, Options.Environment, "pdf", name);
            File.WriteAllBytes(file, data);
            return file;
        }

        string FileNamePart()
        {
            string[] tokens = Options.Document.Split('-');
            if (!tokens[1].StartsWith("R"))            
            {
                tokens[3] = tokens[3].PadLeft(8, '0');
            }
            var name = string.Join("-", tokens);
            return name;
        }

        void Log(params object[] messages)
        {
            if (Options.Verbose)
            {
                Console.WriteLine(DateTime.Now);
                foreach (var message in messages)
                {
                    Console.WriteLine(message.ToString());
                }
                Console.WriteLine();
            }
        }
    }



}
