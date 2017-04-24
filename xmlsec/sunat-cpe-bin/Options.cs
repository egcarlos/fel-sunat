using CommandLine;
using CommandLine.Text;

namespace CPE
{
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

        [Option('w', "workdir", DefaultValue = "D:\\fel\\files")]
        public string Workdir { get; set; }

        [HelpOption]
		public string GetUsage()
		{
			return HelpText.AutoBuild(this,
			  (HelpText current) => HelpText.DefaultParsingErrorsHandler(this, current));
		}

	}
}
