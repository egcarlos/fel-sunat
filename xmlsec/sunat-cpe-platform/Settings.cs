namespace CPE.Platform
{
    public class Settings
    {
        public string Enviroment { get; set; }
        public string Issuer { get; set; }
        public string InvoicePath { get; set; }
        public string CertificatePath { get; set; }
        public string DespatchPath { get; set; }
        public string QueryPath { get; set; }
        public string KeyStorePass { get; set; }
        public string SunatUser { get; set; }
        public string SunatPass { get; set; }

        override public string ToString()
        {
            string template = @"Settings[
    Issuer = {1}
    Enviroment = {0}
    InvoicePath = {2}
    CertificatePath = {3}
    DespatchPath = {4}
    QueryPath = {5}
    KeyStorePass = {6}
    SunatUser = {7}
    SunatPass = {8}
]";
            return string.Format(template, Enviroment, Issuer, InvoicePath, CertificatePath, DespatchPath, QueryPath, "****", SunatUser, "****");
        }
    }
}
