using System;
using System.IO;
using System.Configuration;
using System.Security.Cryptography.X509Certificates;
using SunatClient.Sunat;
using System.Linq;

namespace Nutria.CPE.Bin
{
    class Program
    {
        static string PatchTemplate(string key, params object[] args)
        {
            string template = ConfigurationManager.AppSettings.Get(key);
            return string.Format(template, args);
        }

        static void Main(string[] args)
        {
            if (args.Length < 3)
            {
                Console.WriteLine("uso: programa <ambiente> <comando> <id> [<argumentos>]");
                Console.WriteLine();
                Console.WriteLine("    <ambiente> := testing | qa | live");
                Console.WriteLine("    <comando>  := query | ticket | declare | sendzip");
                Console.WriteLine("    <id>       := <ruc>-<tipo>-<serie>-<numero>");
                Console.WriteLine("    <argumentos> varía segun el comando");
                return;
            }

            var enviroment = args[0];
            var command = args[1];
            var id = args[2];
            switch (command)
            {
                case "query":
                    Query(enviroment, id);
                    break;
                case "ticket":
					if (args.Length != 4)
					{
						Console.WriteLine("uso: programa <ambiente> ticket <id> <numero de ticket>");
						return;
					}
                    var ticket = args[3];
                    Ticket(enviroment, id, ticket);
                    break;
                case "declare":
                    var type = id.Split('-')[1];
                    if (type.In("RR", "RC", "RA"))
                    {
                        DeclareSummary(enviroment, id);
                    }
                    else
                    {
                        DeclareDocument(enviroment, id);
                    }
                    break;
				case "sendzip":
					if (args.Length != 4)
					{
						Console.WriteLine("uso: programa <ambiente> ticket <id> <archivo zip>");
						return;
					}
					break;
            }

            /*if ("sendzip" == args[1])
            {
                Console.WriteLine(DateTime.Now);
                Console.WriteLine("Declarando Documento");
                byte[] response = sclient.sendBill(conf.Name + ".zip", File.ReadAllBytes(conf.Name + ".zip"));
                //sclient.Close();
                File.WriteAllBytes(conf.SunatResponseZipPath, response);
                sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
                sunatzip.Load(conf.SunatResponseXmlPath);
                //ENVIO DEL MENSAJE DE SUNAT
                Console.WriteLine(DateTime.Now);
                Console.WriteLine(sunatzip.Description);
            }*/
        }

        private static void DeclareDocument(string enviroment, string id)
        {
            /*
             * Preparacion de requerimientos
             */
            var type = id.Split('-')[1];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var sclient = new ClientManager(enviroment, type, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            var client = new Tools.Platform.JSONRestClient(conf.PlatformApiURL);
            var sunatzip = new Tools.SUNATResponse();

            /*
             * Firma Digital del documento
             */
            var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
            var keyManager = new Tools.Security.PKCS12KeyManager(pk12);
            var sign = new Tools.SignProcess(conf, keyManager, client);
            sign.Execute();

            /*
             * Requerimiento de validacion a SUNAT. Se envía el archivo zip acompañado de su nombre de archivo.
             * 
             * Nombre de archivo = <identificador>.zip
             * Datos = bytes leidos del archivo zip generado
             * 
             */
            try
            {
				Response("Declarando Documento", "Endpoint: " + sclient.Endpoint.Address.Uri, "");
				var requestName = conf.Name + ".zip";
				var requestData = File.ReadAllBytes(conf.SunatRequestZipPath);
				byte[] response;
                response = sclient.sendBill(requestName, requestData);
				File.WriteAllBytes(conf.SunatResponseZipPath, response);
				sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
				sunatzip.Load(conf.SunatResponseXmlPath);
				Response("RESPUESTA " + sunatzip.Description);
				client.UpdateSunatResponse(id, DateTime.Now, "0".Equals(sunatzip.ResponseCode) ? "declarado" : "rechazado", sunatzip.Description, sclient.Endpoint.Address.Uri.ToString(), null);
				var pdfclient = new System.Net.WebClient();
				pdfclient.DownloadFile(conf.PdfURL, conf.PdfPath);
            }
            catch (System.ServiceModel.FaultException ex)
            {
				HandleFaultException(ex, client, id, sclient.Endpoint.Address.Uri.ToString());
            }
            catch (Exception ex)
            {
				HandleGeneralException(ex, client, id, sclient.Endpoint.Address.Uri.ToString());
            }
        }

        private static void DeclareSummary(string enviroment, string id)
        {
            /*
             * Preparacion de requerimientos
             */
            var type = id.Split('-')[1];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var sclient = new ClientManager(enviroment, type, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            var name = conf.Name + ".zip";
            var client = new Tools.Platform.JSONRestClient(conf.PlatformApiURL);
            
			/*
             * Firma Digital del documento
             */
            var pk12 = new X509Certificate2(conf.KSPath, conf.KSPass);
            var keyManager = new Tools.Security.PKCS12KeyManager(pk12);
            var sign = new Tools.SignProcess(conf, keyManager, client);
            sign.Execute();

            try
            {
                Response("Declarando Resumen", "Endpoint: " + sclient.Endpoint.Address.Uri, "");
                var ticket = sclient.sendSummary(name, File.ReadAllBytes(conf.SunatRequestZipPath));
                client.UpdateSunatResponse(id, DateTime.Now, "enviado", "Ticket recibido", sclient.Endpoint.Address.Uri.ToString(), ticket);
                Response("TICKET " + ticket);
            }
			catch (System.ServiceModel.FaultException ex)
			{
				HandleFaultException(ex, client, id, sclient.Endpoint.Address.Uri.ToString());
			}
			catch (Exception ex)
			{
				HandleGeneralException(ex, client, id, sclient.Endpoint.Address.Uri.ToString());
			}
        }

        private static void Ticket(string enviroment, string id, string ticket)
        {
            //prepare
            var type = id.Split('-')[1];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var sclient = new ClientManager(enviroment, type, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            var sunatzip = new Tools.SUNATResponse();
            //operation
            sclient.Open();
            var response = sclient.getStatus(ticket);
            sclient.Close();
            File.WriteAllBytes(conf.SunatResponseZipPath, response.content);
            sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
			sunatzip.Load(conf.SunatResponseXmlPath);
            //response
            Response(response.statusCode);
        }

        private static void Query(string enviroment, string id)
        {
            //prepare
            var type = id.Split('-')[1];
            var serial = id.Split('-')[2];
            var number = id.Split('-')[3];
            var conf = new Tools.Configuration(ConfigurationManager.AppSettings, id);
            var qclient = new SunatClient.SunatQuery.ClientManager(enviroment, conf.RUC, conf.SunatUser, conf.SunatPass).Proxy;
            var sunatzip = new Tools.SUNATResponse();
            //operation
            var response = qclient.getStatusCdr(conf.RUC, type, serial, int.Parse(number));
            //response
            Response(response.statusCode + " " + response.statusMessage);
            if (response.content != null && response.content.Length > 0)
            {
                File.WriteAllBytes(conf.SunatResponseZipPath, response.content);
                sunatzip.Unzip(conf.Name, conf.SunatResponseZipPath, conf.SunatResponseXmlPath);
                sunatzip.Load(conf.SunatResponseXmlPath);
                Response("RC:" + sunatzip.ResponseCode + " - " + sunatzip.Description);
            }
            else
            {
                Response("No se adjunta CDR");
            }
        }

		/// <summary>
		/// Controla la aparicion de excepciones del tipo FaultException al llamar al servicio web.
		/// </summary>
		/// <param name="ex">Ex.</param>
		/// <param name="client">Client.</param>
		/// <param name="id">Identifier.</param>
		/// <param name="endpoint">Endpoint.</param>
		private static void HandleFaultException(System.ServiceModel.FaultException ex, Tools.Platform.JSONRestClient client, string id, string endpoint)
		{
			var r = ex.Code.Name + "-" + ex.Code.SubCode;
			Response(r);
			client.UpdateSunatResponse(id, DateTime.Now, "error", r, endpoint, null);
		}

		/// <summary>
		/// Controla la aparición de una excepción de indole general en el proceso. Considera el caso donde los
		/// errores de sunat aparecen como errores generales. 
		/// </summary>
		/// <param name="ex">Ex.</param>
		private static void HandleGeneralException(Exception ex, Tools.Platform.JSONRestClient client, string id, string endpoint)
		{
			//SOLO APLICA EN LLAMADAS A SUNAT
			string msg;
			msg = ex.InnerException != null ? string.Concat(ex.InnerException.Message, ex.Message) : ex.Message;
			var tag = "<faultcode>";
			if (msg.Contains(tag))
			{
				var posicion = msg.IndexOf(tag, StringComparison.Ordinal);
				var codigoError = msg.Substring(posicion + tag.Length, 4);
				msg = $"El Código de Error es {codigoError}";
			}
			var r = msg;
			Response(r);
			client.UpdateSunatResponse(id, DateTime.Now, "error", r, endpoint, null);
			return;
		}

        private static void Response(params string[] args)
        {
            Console.WriteLine(DateTime.Now);
            foreach (var arg in args)
            {
                Console.WriteLine(arg);
            }
        }
    }

}



