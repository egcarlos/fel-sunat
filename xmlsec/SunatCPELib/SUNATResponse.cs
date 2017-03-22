using System.IO;
using System.IO.Compression;
using System.Linq;
using System.Xml;

namespace Nutria.CPE.Tools
{
    public class SUNATResponse
    {
        public XmlDocument Document { get; private set; }
        public string ResponseCode { get; private set; }
        public string Description { get; private set; }

        public bool Unzip(string name, string sunatResponseFile, string unzippedResponseFile)
        {
            using (var zip = new FileStream(sunatResponseFile, FileMode.Open))
            {
                using (ZipArchive archive = new ZipArchive(zip, ZipArchiveMode.Read))
                {
                    //TODO hay que verificar como identificar el caso de estar con una respuesta de resumen de baja de retenciones
					var entry = archive.Entries.FirstOrDefault(a => a.Name.StartsWith("R-", System.StringComparison.OrdinalIgnoreCase));
                    if (entry == null)
                    {
                        //estamos analizando retenciones
                        entry = archive.Entries.FirstOrDefault(a => a.Name.StartsWith(name, System.StringComparison.OrdinalIgnoreCase));
                    }
                    entry.ExtractToFile(unzippedResponseFile);
                    return true;
                }
            }
        }

        public bool Load (string unzippedResponseFile)
        {
            Document = new XmlDocument();
            Document.Load(unzippedResponseFile);

            ResponseCode = Document.GetElementsByTagName("ResponseCode", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2")[0].InnerText;
            Description = Document.GetElementsByTagName("Description", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2")[0].InnerText;
            
            return true;
        }
    }
}
