using System;
using System.IO;
using Microsoft.VisualStudio.TestTools.UnitTesting;
using System.ServiceModel;
using System.ServiceModel.Security;
using System.ServiceModel.Dispatcher;
using System.ServiceModel.Channels;
using System.ServiceModel.Description;

namespace org.nutria.sunat.service
{
    [TestClass]
    public class PasswordTest
    {
        [TestMethod]
        public void ClientBetaTest()
        {
            try
            {
                var client = new beta.billServiceClient();
                //client.ClientCredentials.UserName.UserName = "20100318696MODDATOS";
                //client.ClientCredentials.UserName.Password = "MODDATOS";
                client.Endpoint.EndpointBehaviors.Add(new SecurityBehavior()
                {
                    Username = "20100318696MODDATOS", Password = "moddatos"
                }
                );
                
                byte[] response = client.sendBill("20100318696-20-R001-00000002.zip", File.ReadAllBytes(@"F:\fel\files\20100318696\documentos\20100318696-20-R001-00000002.sunat.zip"));
                
                using (var sout = new FileStream("salida.out", FileMode.Create))
                {
                    sout.Write(response, 0, response.Length);
                }
            }
            catch (FaultException ex)
            {
                var fault = ex.CreateMessageFault();
                var detail = fault.GetReaderAtDetailContents().Value;
                throw ex;
            }
            
        }

    }

}
