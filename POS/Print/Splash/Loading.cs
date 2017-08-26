using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Threading;
using System.Configuration;
using System.Windows.Forms;

namespace Print.Splash
{
    public partial class Loading : Form
    {
        public Loading()
        {
            InitializeComponent();
        }

        private void Loading_Shown(object sender, EventArgs e)
        {
            var t = new Thread(new ThreadStart(ConfigPrinter));
            t.Start();
        }

        private void ConfigPrinter()
        {
            var printer = Controllers.PrinterHolder.Instance;
            printer.Connect(Settings.Default.Printer);
            printer.PrintLine("Initialized");
            printer.Cut();
            this.Invoke(new MethodInvoker(ShowMainForm));
        }

        private void ShowMainForm()
        {
            this.Hide();
            new Forms.Receipts().Show();
        }

        private void Loading_Load(object sender, EventArgs e)
        {

        }


    }
}
