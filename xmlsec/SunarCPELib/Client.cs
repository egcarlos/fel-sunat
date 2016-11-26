using System;
using System.Collections.Generic;
using RestSharp;

namespace Nutria.CPE.Tools.Platform
{
    /// <summary>
    /// Contrato para actualizacion de datos en la plataforma
    /// </summary>
    public interface IPlatformClient
    {

        /// <summary>
        /// Consulta documentos usando el estado como parametro
        /// </summary>
        /// <param name="status"></param>
        /// <returns>resultado de la operacion</returns>
        List<Dictionary<string, string>> Pendings(string status = null);

        /// <summary>
        /// Envia los resultados del proceso de firma al servidor
        /// </summary>
        /// <param name="name">Identificador del documento procesado</param>
        /// <param name="date">Fecha de ejecucion del proceso</param>
        /// <param name="signatureValue">Valor de la firma</param>
        /// <param name="digestValue">Valor del hash</param>
        /// <returns>resultado de la operacion</returns>
        bool UpdateSignature(string name, DateTime date, string signatureValue, string digestValue);

        /// <summary>
        /// Envia las respuestas de SUNAT
        /// </summary>
        /// <param name="name">Identificador del documento procesado</param>
        /// <param name="date">Fecha de ejecucon del proceso</param>
        /// <param name="status">Estado de sunat</param>
        /// <param name="message">Mensaje de SUNAT</param>
        /// <param name="endpoint">URL de SUNAT</param>
        /// <returns>resultado de la operacion</returns>
        bool UpdateSunatResponse(string name, DateTime date, string status, string message, string endpoint, string ticket);

    }

    /// <summary>
    /// Implementacion del cliente de plataforma en formato de requerimiento por Query String y cuerpos JSON.
    /// </summary>
    public class JSONRestClient : IPlatformClient
    {
        public string DateFormat { get; private set; }

        public RestClient Client { get; private set; }

        public JSONRestClient(string baseUrl)
        {
            this.Client = new RestClient(baseUrl);
            this.DateFormat = "yyyy-MM-dd HH:mm:ss";
        }

        public List<Dictionary<string, string>> Pendings(string status = null)
        {
            var request = new RestRequest(Method.GET);
            request.AddQueryParameter("status", status);
            var response = Client.Execute<List<Dictionary<string, string>>>(request);
            return response.Data;
        }

        public bool UpdateSignature(string name, DateTime date, string signatureValue, string digestValue)
        {
            var request = new RestRequest(name, Method.POST) { RequestFormat = DataFormat.Json };
            request.AddQueryParameter("mode", "signature");
            request.AddBody(new Dictionary<string, string>() {
                { "signatureValue", signatureValue },
                { "digestValue", digestValue },
                { "date", date.ToString(this.DateFormat) }
            });
            var response = Client.Execute<bool>(request);
            return response.Data;
        }

        public bool UpdateSunatResponse(string name, DateTime date, string status, string message, string endpoint, string ticket)
        {
            var request = new RestRequest(name, Method.POST) { RequestFormat = DataFormat.Json };
            request.AddQueryParameter("mode", "sunat");
            request.AddBody(new Dictionary<string, string>() {
                { "status", status },
                { "message", message },
                { "date", date.ToString(this.DateFormat) },
                { "endpoint", endpoint },
                { "ticket", ticket }
            });
            var response = Client.Execute<bool>(request);
            return true;
        }
    }
}
