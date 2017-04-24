using System;
using System.Collections.Generic;
using System.Xml;
using System.Text;
using System.Threading.Tasks;
using CommandLine;
using CommandLine.Text;

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
            }
        }

        void Prepare()
        {
            Platform = new CPE.Platform.Client() { BaseUrl = Options.Platform };
            //set the issuer based on the document if present
            if (Options.Document != null)
            {
                Options.Issuer = "6-" + Options.Document.Split('-')[0];
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
            var manager = new CPE.Client.QueryClientManager(CurrentSettings.QueryPath, CurrentSettings.SunatUser, CurrentSettings.SunatPass);
            try
            {
                var response = manager.GetCDR(Options.Document);
                Log("Status Code: " + response.statusCode);
                var ccdr = response.content;
                HandleCompressedCDR(ccdr);
            }
            finally
            {
                Log("Request:", manager.Behaviors.Messages.Request, "Reply:", manager.Behaviors.Messages.Reply);
            }
        }

        void Declare()
        {
            Log("Using settings", CurrentSettings);
            string endpoint = Options.Document.Split('-')[1].AsDeclareTarget(CurrentSettings);
            var manager = new CPE.Client.DeclareClientManager(endpoint, CurrentSettings.SunatUser, CurrentSettings.SunatPass);
            byte[] requestFile = GetRequestFile();
            byte[] compressed = Compress(requestFile, Options.Document + ".xml");
            PersistFile(compressed, Options.Document + ".request.zip");
            byte[] ccdr = manager.Declare(Options.Document + ".zip", compressed);
            HandleCompressedCDR(ccdr);
        }

        void HandleCompressedCDR(byte[] ccdr)
        {
            byte[] cdr = ccdr.UnzipResponse();
			var document = new XmlDocument();
            document.Load(cdr.AsStream());
			var responseCode = document.GetElementsByTagName("ResponseCode", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2")[0].InnerText;
			var description = document.GetElementsByTagName("Description", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2")[0].InnerText;
            Platform.UpdateCDRResponse(CurrentSettings.Enviroment, Options.Document, responseCode, description);
            Log("Response Code: " + responseCode, "Description: " + description);
		}

		

		byte[] GetRequestFile()
		{
            var document = Platform.GetPlainDocument(Options.Environment, Options.Document);

            return document.GetBytes();
		}

		byte[] Compress(byte[] data, string entry)
		{
			return null;
		}

		void PersistFile(byte[] data, string name)
		{
			string workdir = "D:\\fel\\files\\";

            
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
