using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using RestSharp;

namespace CPE.Platform
{


    public class Client
    {
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
            var request = new RestRequest("api/settings", Method.GET);
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
    }
}
