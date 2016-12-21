using System.Linq;
using System.Net;
using SunatClient.Security;

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
            Proxy.Endpoint.EndpointBehaviors.Add(new PasswordDigestBehavior() { RUC = ruc, User = user, Password = password });
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
            if (documentType.In("20", "40"))
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