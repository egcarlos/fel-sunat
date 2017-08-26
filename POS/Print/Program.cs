using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows.Forms;

namespace Print
{
    static class Program
    {
        /// <summary>
        /// Punto de entrada principal para la aplicación.
        /// </summary>
        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.ApplicationExit += Application_ApplicationExit;
            Application.Run(new Splash.Loading());
        }

        private static void Application_ApplicationExit(object sender, EventArgs e)
        {
            Controllers.PrinterHolder.Instance.Release();
        }
    }
}
