namespace FEL.CPE.Tools
{
    class Foo
    {
        public void Main()
        {
            var client = new ClienteDemo();
            var id = "";
            //obtener los datos desde la librería de firma
            var data = new byte[1024];

            var response = client.SendDocument(id, data);

            System.Console.WriteLine("{0}: {1}", "Documento: ", response.document);
            System.Console.WriteLine("{0}: {1}", "Hash:      ", response.hash);
            System.Console.WriteLine("{0}: {1}", "Firma:     ", response.signature);
        }

        public void main1 ()
        {
var id = "";
var client = new RestSharp.RestClient("https://test.ecco.pe/fel");
var request = new RestSharp.RestRequest("{resource}", RestSharp.Method.GET);
request.AddUrlSegment("resource", id + ".png");
var data = client.DownloadData(request);
//hacer algo con la data descargada
        }
    }

    /// <summary>
    /// Clase para contener la respuesta del servidor.
    /// </summary>
    class ServerResponse
    {
        /// <summary>
        /// Identificador del documento consultado
        /// </summary>
        public string document { get; set; }
        /// <summary>
        /// Estado del documento
        /// </summary>
        public string status { get; set; }
        /// <summary>
        /// Mensajo de error asociado solo cuando el estado es error
        /// </summary>
        public string errorMessage { get; set; }
        /// <summary>
        /// Firma digital en Base64
        /// </summary>
        public string signature { get; set; }
        /// <summary>
        /// Hash del documento en Base64
        /// </summary>
        public string hash { get; set; }
        /// <summary>
        /// Código de respuesta de sunat
        /// </summary>
        public string sunat { get; set; }
    }

    /// <summary>
    /// Clase de ejemplo para consumo de servicios en el servidor fel
    /// </summary>
    class ClienteDemo
    {
        private RestSharp.RestClient client { get; set; }

        public ClienteDemo()
        {
            this.client = new RestSharp.RestClient("https://test.ecco.pe/fel");
        }

        /// <summary>
        /// Envía un documento al servidor de factura electrónica.
        /// </summary>
        /// <param name="identifier">Identificador del documento</param>
        /// <param name="documentData">Data binaria que representa al XML/UBL.
        /// Se obtiene de la librería de firma o del builder de documentos.</param>
        /// <returns></returns>
        public ServerResponse SendDocument(string identifier,byte[]documentData)
        {   
            var request = new RestSharp.RestRequest("{id}",RestSharp.Method.POST);
            request.AddUrlSegment("id", identifier);
            request.AddHeader("Content-Type", "application/octet-stream");
            request.AddHeader("Accept", "application/json");
            request.AddParameter(
                "application/octet-stream",
                documentData,
                RestSharp.ParameterType.RequestBody
            );
            var response = this.client.Execute<ServerResponse>(request);
            return response.Data;
        }

        /// <summary>
        /// Consulta el estado de un documento en el servidor.
        /// </summary>
        /// <param name="identifier"></param>
        /// <returns></returns>
        public ServerResponse QueryDocument(string identifier)
        {   
            var request = new RestSharp.RestRequest("{id}", RestSharp.Method.GET);
            request.AddUrlSegment("id", identifier);
            request.AddHeader("Accept", "application/json");
            var response = this.client.Execute<ServerResponse>(request);
            return response.Data;
        }
    }
}
