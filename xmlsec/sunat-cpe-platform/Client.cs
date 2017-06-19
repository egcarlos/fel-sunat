using System;
using System.Collections.Generic;
using System.Xml;
using RestSharp;
using System.Net;

namespace CPE.Platform
{


    public class Client
    {
        const string DateTimeFormat = "yyyy-MM-dd HH:mm:ss";

        public static string Now
        {
            get
            {
                return System.DateTime.Now.ToString(DateTimeFormat);
            }
        }

        public string BaseUrl { get; set; }

        public List<Settings> Settings(string issuer, string environment)
        {
            var client = new RestClient(BaseUrl);
            var resource = "api/settings";
            if (issuer != null)
            {
                resource += "/{issuer}";
                if (environment != null)
                {
                    resource += "/{environment}";
                }
            }
            var request = new RestRequest(resource, Method.GET);
            if (issuer != null)
            {
                request.AddUrlSegment("issuer", issuer);
                if (environment != null)
                {
                    request.AddUrlSegment("environment", environment);
                }
            }
            var response = client.Execute<List<Settings>>(request);

            return response.Data;
        }

        public bool UpdateSignature(string environment, string documentId, String hash, String signature)
        {
            var data = new Dictionary<String, String>() {
                {"firma_fecha", System.DateTime.Now.ToString(DateTimeFormat)},
                {"firma_hash", hash},
                {"firma_valor", signature}
            };
            return UpdateDocument(environment, documentId, data);
        }

        public bool UpdateError(string environment, string documentId, String code, String message)
        {
            var data = new Dictionary<String, String>() {
                {"proceso_fecha", System.DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss.sss")},
                {"proceso_estado", "E"},
                {"proceso_mensaje", "Error en llamar al servicio"},
                {"sunat_fecha", System.DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss.sss")},
                {"sunat_estado", code},
                {"sunat_mensaje", message}
            };
            return UpdateDocument(environment, documentId, data);
        }

        public bool UpdateCDRResponse(string environment, string documentId, String code, String message)
        {
            var data = new Dictionary<String, String>() {
                {"proceso_fecha", System.DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss.sss")},
                {"proceso_estado", "0" == code? "P" : "R"},
                {"proceso_mensaje", "Verificar respuesta de sunat."},
                {"sunat_fecha", System.DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss.sss")},
                {"sunat_estado", "0" == code? "declarado" : "rechazado"},
                {"sunat_mensaje", message + " (codigo:" + code + ")"}
            };
            return UpdateDocument(environment, documentId, data);
        }

        public bool UpdateTicket(string environment, string documentId, String ticket)
        {
            var data = new Dictionary<String, String>() {
                {"sunat_fecha", System.DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss.sss")},
                {"sunat_estado", "-"},
                {"sunat_mensaje", "Esperando respuesta de Ticket"},
                {"sunat_ticket", ticket}
            };
            return UpdateDocument(environment, documentId, data);
        }

        public bool UpdateDocument(string environment, string documentId, Dictionary<String, String> data)
        {
            var client = new RestClient(BaseUrl);
            var resource = "api/document/{environment}/{documentId}";
            var request = new RestRequest(resource, Method.POST) { RequestFormat = DataFormat.Json };
            request.AddUrlSegment("environment", environment);
            request.AddUrlSegment("documentId", documentId);
            request.AddBody(data);
            var response = client.Execute<bool>(request);
            return response.Data;
        }

        public XmlDocument GetPlainDocument(string environment, string documentId)
        {
            var document = new XmlDocument() { PreserveWhitespace = true };
            var path = BaseUrl + "/xml/load.php?name=" + documentId + "&env=" + environment;
            document.Load(path);
            return document;
        }

        public byte[] GetPDF(string environment, string documentId)
        {
            var client = new WebClient();
            var path = BaseUrl + "/pdf/pdf.php?name=" + documentId + "&env=" + environment + "&pl=L&s=A5&c=2";
            var pdf = client.DownloadData(path);
            return pdf;
        }
    }
}
