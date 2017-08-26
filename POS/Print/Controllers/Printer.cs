using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Microsoft.PointOfService;

namespace Print.Controllers
{
    public sealed class PrinterHolder
    {
        private static volatile Printer instance;
        private static object syncRoot = new Object();

        private PrinterHolder() { }

        public static Printer Instance
        {
            get
            {
                if (instance == null)
                {
                    lock (syncRoot)
                    {
                        if (instance == null)
                            instance = new Printer();
                    }
                }

                return instance;
            }
        }
    }

    public class PrinterConstants
    {
        public const string Cut = "\u001b|fP";
        public const string LineFeed = "\n";
        //normalize all settings
        public const string Normal = "\u001b|N";

        //Font styles
        public const string Wide = "\u001b|2C";
        public const string Bold = "\u001b|bC";
        public const string Underline = "\u001b|uC";

        //Text Align
        public const string Center = "\u001b|cA";
        public const string Right = "\u001b|rA";

    }

    public enum LineStyles
    {
        Wide,
        Bold,
        Underline,
        Center,
        Right
    }

    public class Printer
    {

        public static Printer Instance { get; private set; }
        public PosPrinter Device { get; private set; }
        public DeviceInfo Info { get; private set; }
        private List<LineStyles> Styles { get; set; } = new List<LineStyles>();

        //STYLE MANAGEMENT

        public Printer Style(LineStyles style) {
            if (!this.Styles.Contains(style))
            {
                this.Styles.Add(style);
            }
            return this;
        }

        public Printer Clear(LineStyles style) {
            this.Styles.RemoveAll(item => item == style);
            return this;
        }
        
        public Printer ClearStyles()
        {
            this.Styles.Clear();
            return this;
        }

        private void ApplyStyles(StringBuilder sb)
        {
            foreach (var style in this.Styles)
            {
                switch (style)
                {
                    case LineStyles.Bold: sb.Append(PrinterConstants.Bold); break;
                    case LineStyles.Center: sb.Append(PrinterConstants.Center); break;
                    case LineStyles.Right: sb.Append(PrinterConstants.Right); break;
                    case LineStyles.Underline: sb.Append(PrinterConstants.Underline); break;
                    case LineStyles.Wide: sb.Append(PrinterConstants.Wide); break;
                }
            }
        }

        //PRINTING METHODS

        public void Print(String s)
        {
            this.Device.PrintNormal(PrinterStation.Receipt, s);
        }

        public Printer PrintLine()
        {
            this.Print(PrinterConstants.LineFeed);
            return this;
        }

        public Printer PrintLine(string s)
        {
            var sb = new StringBuilder();
            this.ApplyStyles(sb);
            sb
                .Append(s)
                .Append(PrinterConstants.Normal)
                .Append(PrinterConstants.LineFeed);
            this.Print(sb.ToString());
            return this;
        }

        public Printer PrintLines (params string[] lines)
        {
            foreach (var line in lines)
            {
                this.PrintLine(line);
            }
            return this;
        }

        public Printer PrintTitle(string title)
        {
            PrintLine(PrinterConstants.Wide + PrinterConstants.Center + title);
            return this;
        }

        public void PrintItem(string description, string currency, double price)
        {
            string left = description.Trim();
            string right = currency + " " + price.ToString("F");
            this.Print(left);
            this.Print(PrinterConstants.Right);
            this.Print(right);
            this.Print(PrinterConstants.Normal);
            this.PrintLine();
        }

        public Printer Cut()
        {
            this.Print(PrinterConstants.Cut);
            return this;
        }

        public void Connect(string deviceName)
        {
            var explorer = new PosExplorer();
            try
            {
                this.Info = explorer.GetDevice(DeviceType.PosPrinter, deviceName);
                if (this.Info == null)
                {
                    return;
                }
                this.Device = (PosPrinter)explorer.CreateInstance(this.Info);
            }
            catch (PosException)
            {
            }
            this.Device.Open();
            this.Device.Claim(1000);
            this.Device.DeviceEnabled = true;
        }

        public void Release()
        {
            if (this.Device != null)
            {
                try
                {
                    this.Device.DeviceEnabled = false;
                    this.Device.Release();
                }
                catch (PosControlException)
                {
                }
                finally
                {
                    this.Device.Close();
                }
            }
        }
    }
}
