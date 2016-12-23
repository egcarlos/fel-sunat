using System;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.ServiceModel.Description;
using System.ServiceModel.Dispatcher;

namespace SunatClient.Logging
{

    public class MessageLogger : IClientMessageInspector
    {
        public object BeforeSendRequest(ref Message request, IClientChannel channel)
        {
            Console.WriteLine(DateTime.Now);
            Console.WriteLine(request.ToString());
            Console.WriteLine();
            return null;
        }

        public void AfterReceiveReply(ref Message reply, object correlationState)
        {
            Console.WriteLine(DateTime.Now);
            Console.WriteLine(reply.ToString());
            Console.WriteLine();
        }
    }

    public class LogBehavior : IEndpointBehavior
    {
        public void Validate(ServiceEndpoint endpoint)
        {
        }

        public void AddBindingParameters(ServiceEndpoint endpoint, BindingParameterCollection bindingParameters)
        {
        }

        public void ApplyDispatchBehavior( ServiceEndpoint endpoint, EndpointDispatcher endpointDispatcher)
        {
        }

        public void ApplyClientBehavior( ServiceEndpoint endpoint, ClientRuntime clientRuntime)
        {
            var logger = new MessageLogger();
            clientRuntime.ClientMessageInspectors.Add(logger);
        }
    }
}
