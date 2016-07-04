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

namespace org.nutria.sunat.service
{
    public class SecurityBehavior : IEndpointBehavior
    {
        public string Username { get; set; }
        public string Password { get; set; }

        public void AddBindingParameters(ServiceEndpoint endpoint, BindingParameterCollection bindingParameters)
        {
        }

        public void ApplyClientBehavior(ServiceEndpoint endpoint, ClientRuntime clientRuntime)
        {

            clientRuntime.ClientMessageInspectors.Add(new AddMessageHeaderInspector()
            {
                Header = new SecurityHeader { Username = this.Username, Password = this.Password }
            }
            );
        }

        public void ApplyDispatchBehavior(ServiceEndpoint endpoint, EndpointDispatcher endpointDispatcher)
        {
        }

        public void Validate(ServiceEndpoint endpoint)
        {
        }
    }

    class AddMessageHeaderInspector : IClientMessageInspector
    {
        public MessageHeader Header { get; set; }

        public void AfterReceiveReply(ref Message reply, object correlationState)
        {
            return;
        }

        public object BeforeSendRequest(ref Message request, IClientChannel channel)
        {
            request.Headers.Add(this.Header);
            return null;
        }
    }

    class SecurityHeader : MessageHeader
    {
        public string Username { get; set; }
        public string Password { get; set; }
        public override string Name { get { return "Security"; } }
        public override string Namespace { get { return "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"; } }
        const string Prefix = "wsse";

        protected override void OnWriteHeaderContents(XmlDictionaryWriter writer, MessageVersion messageVersion)
        {
            writer.WriteStartElement(Prefix, "UsernameToken", Namespace);

            writer.WriteStartElement(Prefix, "Username", Namespace);
            writer.WriteValue(this.Username);
            writer.WriteEndElement();

            writer.WriteStartElement(Prefix, "Password", Namespace);
            writer.WriteValue(this.Password);
            writer.WriteEndElement();

            writer.WriteEndElement();
        }
    }
}
