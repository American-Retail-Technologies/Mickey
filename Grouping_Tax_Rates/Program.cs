using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.IO;

namespace Grouping_Tax_Rates
{
    class Program
    {
        static void Main(string[] args)
        {
            if (args.Length == 1)
            {
                Console.WriteLine("Processing file: " + args[0] + args[1]);
            }
            else
            {
                Console.WriteLine("Invalid input!");
                return;
            }
            StreamReader inputFile1 = new StreamReader(args[0]);
            StreamWriter outputFile = new StreamWriter(args[0] + ".CSV");
        }
    }
}
