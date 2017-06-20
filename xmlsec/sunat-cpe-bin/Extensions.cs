using System.IO;
using System.IO.Compression;
using System.Linq;
using System.Xml;

namespace CPE
{
	public static class Extensions
	{
		public static bool In<T>(this T item, params T[] items)
		{
			if (items == null)
			{
				throw new System.ArgumentNullException(nameof(items));
			}
			return items.Contains(item);
		}

        public static bool IsSummary(this string documentType)
        {
            return documentType.In("RA", "RR", "RC");
        }

		public static string AsDeclareTarget(this string documentType, CPE.Platform.Settings settings)
		{
			if (documentType.In("01", "03", "07", "08", "RC", "RA"))
			{
				return settings.InvoicePath;
			}
			if (documentType.In("20", "40", "RR"))
			{
				return settings.CertificatePath;
			}
			if (documentType.In("09"))
			{
				return settings.DespatchPath;
			}
			throw new System.ArgumentException("Invalid value " + documentType + " for parameter", nameof(documentType));
		}

		public static Stream AsStream(this byte[] data)
		{
			var stream = new MemoryStream();
            var writer = new BinaryWriter(stream);
            writer.Write(data);
            stream.Position = 0;
			return stream;
		}

        public static byte[] ZipRequest(this XmlDocument document, string entryName)
        {
            using (var zipstream = new MemoryStream())
            {
                using (ZipArchive archive = new ZipArchive(zipstream, ZipArchiveMode.Create))
                {
                    var entry = archive.CreateEntry(entryName);
                    using (var eout = entry.Open())
                    {
                        document.Save(eout);
                    }
                }
                return zipstream.ToArray();
            }
        }

		public static byte[] UnzipResponse(this byte[] data)
		{
			using (var input = data.AsStream())
			{
				using (var archive = new ZipArchive(input, ZipArchiveMode.Read))
				{
					var entry = archive.Entries.FirstOrDefault(a => a.Name.StartsWith("R-", System.StringComparison.OrdinalIgnoreCase));
					//error response in case of document type 20 and others
					if (entry == null)
					{
						entry = archive.Entries.FirstOrDefault(a => a.Name.StartsWith("20", System.StringComparison.OrdinalIgnoreCase));
					}
					return entry.AsByteArray();
				}
			}
		}

		public static byte[] AsByteArray(this ZipArchiveEntry entry)
		{
			using (var file = entry.Open())
			{
				using (var target = new MemoryStream())
				{
					file.CopyTo(target);
					return target.ToArray();
				}
			}
		}

        public static byte[] GetBytes(this XmlDocument document)
        {
            using(var stream = new MemoryStream())
            {
                document.Save(stream);
                return stream.ToArray();
            }
        }
	}
}
