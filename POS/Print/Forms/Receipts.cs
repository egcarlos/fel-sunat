using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;
using Print.Controllers;

namespace Print.Forms
{
    public partial class Receipts : Form
    {
        public Receipts()
        {
            InitializeComponent();
        }

        private void Receipts_Load(object sender, EventArgs e)
        {

        }

        private void button1_Click(object sender, EventArgs e)
        {
            var printer = Controllers.PrinterHolder.Instance;

            printer
                .PrintTitle("ECCO CENTER SAC")
                .Style(LineStyles.Center)
                .PrintLines(
                    "RUC 20102097069",
                    "Calle Los Telares Nro. 197",
                    "Urb. Vulcano - ATE"
                )
                .PrintLine()
                .PrintLines(
                    "FACTURA ELECTRÓNICA",
                    "F001-00000001",
                    "2017-08-03 02:11 AM"
                )
                .ClearStyles()
                .PrintLine()
                .PrintLines(
                    "RUC: 20563330709",
                    "GOMEZ-ECHEVARRIA CONSULTORES SRL",
                    "LA MOLINA"
                )
                .PrintLine()
                .PrintLine("Descripción" + PrinterConstants.Right + "Total")
                .PrintLine("---------------------------------")
                .PrintLine("CODART1 (x1)" + PrinterConstants.Right + "80.00")
                .PrintLine("CODART2 (x2)" + PrinterConstants.Right + "130.00")
                .PrintLine("CODGRAT1 (x1)" + PrinterConstants.Right + "0.00")
                .PrintLine("---------------------------------")
                .Style(LineStyles.Right)
                .PrintLine("Op. Gravadas S/ 210.00")
                .PrintLine("Op. Gratuitas S/  90.00")
                .PrintLine("IGV S/  37.80")
                .Style(LineStyles.Wide)
                .PrintLine("TOTAL S/ 247.80")
                .PrintLine()
                .ClearStyles()
                .Style(LineStyles.Center)
                .PrintLines(
                    "Representación impresa del",
                    "comprobante electrónico.",
                    "Autorizado mediante resolución",
                    "000-00000000"
                )
                .PrintLine()
                .ClearStyles()
                .PrintLine("Código de seguridad hash:")
                .PrintLine("123456789123456789123456789")
                .PrintLine()
                .Cut();

        }
    }
}
