using System;
using System.Collections.Generic;
using System.Linq;
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
            string endpoint = Options.Document.Split('-')[1].AsTarget(CurrentSettings);
            var manager = new CPE.Client.DeclareClientManager(endpoint, CurrentSettings.SunatUser, CurrentSettings.SunatPass);
            byte[] unsigned = GetUnsignedFile();
            byte[] signed = SignFile(unsigned);
            byte[] compressed = Compress(signed, Options.Document + ".xml");
            PersistXml(compressed, Options.Document + ".request.zip");
            byte[] ccdr = manager.Declare(Options.Document + ".zip", compressed);
            HandleCompressedCDR(ccdr);
        }

        void HandleCompressedCDR(byte[] cdr)
        {

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

    public class Options
    {
        public const string Configuration = "configuration";
        public const string Declare = "declare";
        public const string Query = "query";
        public const string Ticket = "ticket";

        [Option('p', "platform", DefaultValue = "http://localhost/sunat-cpe")]
        public string Platform { get; set; }

        [Option('a', "action", Required = true)]
        public string Action { get; set; }

        [Option('e', "enviroment", DefaultValue = "dev")]
        public string Environment { get; set; }

        [Option('i', "issuer")]
        public string Issuer { get; set; }

        [Option('d', "document")]
        public string Document { get; set; }

        [Option('v', "verbose", DefaultValue = false)]
        public bool Verbose { get; set; }


        [HelpOption]
        public string GetUsage()
        {
            return HelpText.AutoBuild(this,
              (HelpText current) => HelpText.DefaultParsingErrorsHandler(this, current));
        }

    }

    public static class Extensions
    {
        public static bool In<T>(this T item, params T[] items)
        {
            if (items == null)
            {
                throw new System.ArgumentNullException("items");
            }
            return items.Contains(item);
        }

        public static string AsTarget(this string documentType, CPE.Platform.Settings settings)
        {
            if (documentType.In("01", "03", "07", "08", "RC", "RA"))
            {
                return settings.InvoicePath;
            }
            if (documentType.In("20", "40", "RR"))
            {
                return settings.CertificatePath;
            }
            if (documentType.In("09"))
            {
                return settings.DespatchPath;
            }
            throw new System.ArgumentException("Invalid value " + documentType + " for parameter", "documentType");
        }
    }
}
