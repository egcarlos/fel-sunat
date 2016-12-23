using System.Linq;
using System.Net;
using SunatClient.Security;
using System;

namespace SunatClient.Sunat
{

    public class ClientManager
    {

        public billServiceClient Proxy { get; private set; }


        public ClientManager(string enviroment, string documentType, string ruc, string user, string password)
        {
            ServicePointManager.UseNagleAlgorithm = true;
            ServicePointManager.Expect100Continue = false;
            ServicePointManager.CheckCertificateRevocationList = true;
            var endpoint = enviroment + "." + documentType.AsTarget();
            Proxy = new billServiceClient(endpoint);
            var epb = Proxy.Endpoint.EndpointBehaviors;
            var binding = Proxy.Endpoint.Binding;
            binding.OpenTimeout = new TimeSpan(0, 10, 0);
            binding.CloseTimeout = new TimeSpan(0, 10, 0);
            binding.SendTimeout = new TimeSpan(0, 10, 0);
            binding.ReceiveTimeout = new TimeSpan(0, 10, 0);

            epb.Add(new PasswordDigestBehavior() { RUC = ruc, User = user, Password = password });
            epb.Add(new Logging.LogBehavior());
        }

    }

    public static class Extensions
    {

        public static string AsTarget(this string documentType)
        {
            if (documentType.In("01", "03", "07", "08", "RC", "RA"))
            {
                return "invoice";
            }
            if (documentType.In("20", "40", "RR"))
            {
                return "certificate";
            }
            if (documentType.In("09"))
            {
                return "despatch";
            }
            throw new System.ArgumentException("Invalid value", "documentType");
        }

        public static bool In<T>(this T item, params T[] items)
        {
            if (items == null)
            {
                throw new System.ArgumentNullException("items");
            }
            return items.Contains(item);
        }

    }

}

namespace SunatClient.SunatQuery
{
    public class ClientManager
    {
        public billServiceClient Proxy { get; private set; }

        public ClientManager(string enviroment, string ruc, string user, string password)
        {
            ServicePointManager.UseNagleAlgorithm = true;
            ServicePointManager.Expect100Continue = false;
            ServicePointManager.CheckCertificateRevocationList = true;
            var endpoint = enviroment + ".query";
            Proxy = new billServiceClient(endpoint);
            Proxy.Endpoint.EndpointBehaviors.Add(new PasswordDigestBehavior() { RUC = ruc, User = user, Password = password });
        }

    }
}