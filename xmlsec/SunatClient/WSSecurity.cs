using System;
using System.ServiceModel.Channels;
using System.ServiceModel.Description;
using System.ServiceModel.Dispatcher;
using System.Xml;
using Microsoft.Web.Services3.Security.Tokens;

namespace SunatClient.Sunat
{

    public class PasswordDigestBehavior : IEndpointBehavior
    {

        public string RUC { get; set; }
        public string User { get; set; }
        public string Password { get; set; }

        public PasswordDigestBehavior()
        {
        }

        public void AddBindingParameters(ServiceEndpoint endpoint, BindingParameterCollection bindingParameters)
        {
            return;
        }

        public void ApplyClientBehavior(ServiceEndpoint endpoint, ClientRuntime clientRuntime)
        {
            clientRuntime.ClientMessageInspectors.Add(new PasswordDigestMessageInspector(RUC + User, Password));
        }

        public void ApplyDispatchBehavior(ServiceEndpoint endpoint, EndpointDispatcher endpointDispatcher)
        {
            return;
        }

        public void Validate(ServiceEndpoint endpoint)
        {
            return;
        }
    }


    public class PasswordDigestMessageInspector : IClientMessageInspector
    {
        public string Username { get; set; }
        public string Password { get; set; }

        public PasswordDigestMessageInspector(string username, string password)
        {
            Username = username;
            Password = password;
        }

        public void AfterReceiveReply(ref Message reply, object correlationState)
        {
            return;
        }

        public object BeforeSendRequest(ref Message request, System.ServiceModel.IClientChannel channel)
        {
            UsernameToken token = new UsernameToken(Username, Password, PasswordOption.SendPlainText);

            XmlElement securityToken = token.GetXml(new XmlDocument());

            var nodo = securityToken.GetElementsByTagName("wsse:Nonce").Item(0);
            if (nodo != null) nodo.RemoveAll();

            MessageHeader securityHeader = MessageHeader.CreateHeader("Security",
                "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd",
                securityToken, false);
            request.Headers.Add(securityHeader);

            return Convert.DBNull;
        }
    }
}
