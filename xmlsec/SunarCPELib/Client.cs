using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using RestSharp;

namespace Nutria.CPE.Tools.Platform
{
    public interface IPlatformClient
    {

        List<Dictionary<string, string>> Pendings(string status = null);

        bool UpdateSignature(string name, DateTime date, string signatureValue, string digestValue);

        bool UpdateSunatResponse(string name, DateTime date, string status, string message);

    }

    public class JSONRestClient : IPlatformClient
    {
        public string DateFormat { get; set; }

        public RestClient Client { get; private set; }

        public JSONRestClient(string baseUrl)
        {
            this.Client = new RestClient(baseUrl);
            this.DateFormat = "yyyy-MM-dd HH:mm:ss";
        }

        public List<Dictionary<string, string>> Pendings(string status = null)
        {
            var request = new RestRequest(Method.GET);
            request.AddQueryParameter("status", status);
            var response = Client.Execute<List<Dictionary<string, string>>>(request);
            return response.Data;
        }

        public bool UpdateSignature(string name, DateTime date, string signatureValue, string digestValue)
        {
            var request = new RestRequest(name, Method.POST) { RequestFormat = DataFormat.Json };
            request.AddQueryParameter("mode", "signature");
            request.AddBody(new Dictionary<string, string>() {
                { "signatureValue", signatureValue },
                { "digestValue", digestValue },
                { "date", date.ToString(this.DateFormat) }
            });
            var response = Client.Execute<bool>(request);
            return response.Data;
        }

        public bool UpdateSunatResponse(string name, DateTime date, string status, string message)
        {
            var request = new RestRequest(name, Method.POST) { RequestFormat = DataFormat.Json };
            request.AddQueryParameter("mode", "sunat");
            request.AddBody(new Dictionary<string, string>() {
                { "status", status },
                { "message", message },
                { "date", date.ToString(this.DateFormat) }
            });
            var response = Client.Execute<bool>(request);
            return response.Data;
        }
    }
}
