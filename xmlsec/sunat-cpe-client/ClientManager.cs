using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.ServiceModel;
using System.Text;
using System.Threading.Tasks;

namespace CPE.Client
{
    public abstract class ClientManager<I, C> where C : ClientBase<I> where I:class
    {
        public String Endpoint { get; private set; }
        public Behaviors Behaviors { get; private set; }
        public C Proxy { get; private set; }

        public ClientManager(String endpoint, String user, String password)
        {
            Endpoint = endpoint;
            Behaviors = new Behaviors() { User = user, Password = password };

            //configuracion para que no se rechace la respuesta de sunat
            ServicePointManager.UseNagleAlgorithm = true;
            ServicePointManager.Expect100Continue = false;
            ServicePointManager.CheckCertificateRevocationList = true;

            Proxy = BuildClient();
            Proxy.Endpoint.Address = new EndpointAddress(Endpoint);
            Proxy.Endpoint.EndpointBehaviors.Add(Behaviors);
            var binding = Proxy.Endpoint.Binding;
            binding.OpenTimeout = new TimeSpan(0, 10, 0);
            binding.CloseTimeout = new TimeSpan(0, 10, 0);
            binding.SendTimeout = new TimeSpan(0, 10, 0);
            binding.ReceiveTimeout = new TimeSpan(0, 10, 0);
        }

        protected abstract C BuildClient();
    }

    public class DeclareClientManager : ClientManager<Declare.billService, Declare.billServiceClient>
    {
        public DeclareClientManager(string endpoint, string user, string password) : base(endpoint, user, password) { }

        override protected Declare.billServiceClient BuildClient()
        {
            return new Declare.billServiceClient();
        }

        public byte[] Declare (string documentId, byte[] content)
        {
            var response = Proxy.sendBill(documentId + ".zip", content);
            return response;
        }

        public string DeclareSummary(string documentId, byte[] content)
        {
            var response = Proxy.sendSummary(documentId + ".zip", content);
            return response;
        }

        public byte[] QueryTicket(String ticket)
        {
            var response = Proxy.getStatus(ticket);
            return response.content;
        }
    }

    public class QueryClientManager : ClientManager<Query.billService, Query.billServiceClient>
    {

        public QueryClientManager(string endpoint, string user, string password) : base(endpoint, user, password) { }

        override protected Query.billServiceClient BuildClient()
        {
            return new Query.billServiceClient();
        }

        public Query.statusResponse GetCDR(string documentId)
        {
            var args = documentId.Split('-');
            var response = Proxy.getStatusCdr(args[0], args[1], args[2], int.Parse(args[3]));
            return response;
        }
    }
}
