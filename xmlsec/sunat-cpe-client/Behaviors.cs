using System;
using System.Collections.Generic;
using System.Linq;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.ServiceModel.Description;
using System.ServiceModel.Dispatcher;
using System.Text;
using System.Threading.Tasks;
using System.Xml;

namespace CPE.Client
{
    public class Behaviors : IEndpointBehavior
    {

        public string User { get; set; }
        public string Password { get; set; }
        public MessageLogger Messages { get; private set; }

        public Behaviors()
        {
        }

        public void AddBindingParameters(ServiceEndpoint endpoint, BindingParameterCollection bindingParameters)
        {
            return;
        }

        public void ApplyClientBehavior(ServiceEndpoint endpoint, ClientRuntime clientRuntime)
        {
            clientRuntime.ClientMessageInspectors.Add(new PasswordDigestMessageInspector(User, Password));
            clientRuntime.ClientMessageInspectors.Add(Messages = new MessageLogger());
            
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

    public class MessageLogger : IClientMessageInspector
    {
        public string Request { get; private set; }
        public string Reply { get; private set; }

        public object BeforeSendRequest(ref Message request, IClientChannel channel)
        {
            Request = request.ToString();
            return null;
        }

        public void AfterReceiveReply(ref Message reply, object correlationState)
        {
            Reply = reply.ToString();
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

        public object BeforeSendRequest(ref Message request, IClientChannel channel)
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

            return null;
        }
    }
}
