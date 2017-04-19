using System;
using System.ServiceModel.Channels;
using System.ServiceModel.Description;
using System.ServiceModel.Dispatcher;
using System.Xml;
using Microsoft.Web.Services3.Security.Tokens;

namespace SunatClient.Security
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
        const string WSSENameSpace = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";

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
            
            var document = new XmlDocument();
            var usernametoken = document.CreateNode(XmlNodeType.Element, "UsernameToken", WSSENameSpace);
            var username = document.CreateNode(XmlNodeType.Element, "Username", WSSENameSpace);
            username.InnerText = this.Username;
            var password = document.CreateNode(XmlNodeType.Element, "Password", WSSENameSpace);
            password.InnerText = this.Password;
            usernametoken.AppendChild(username);
            usernametoken.AppendChild(password);
            var security = MessageHeader.CreateHeader("Security", WSSENameSpace, usernametoken, false);
            
            request.Headers.Add(security);

            return Convert.DBNull;
        }
    }
}
