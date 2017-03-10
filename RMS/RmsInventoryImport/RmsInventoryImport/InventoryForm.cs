using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices;
using System.Windows.Forms;
using System.Data.SqlClient;
using System.Configuration;

namespace RmsInventoryImport
{
    public partial class InventoryForm : Form
    {
        String storeDataConnectionString = ConfigurationManager.ConnectionStrings["StoreDataConnectionString"].ConnectionString;

        // Get a handle to an application window.
        [DllImport("USER32.DLL", CharSet = CharSet.Unicode)]
        public static extern IntPtr FindWindow(string lpClassName,
            string lpWindowName);

        // Activate an application window.
        [DllImport("USER32.DLL")]
        public static extern bool SetForegroundWindow(IntPtr hWnd);

        CsvParser csvParser = new CsvParser();
        CsvParser csvHeaderParser = new CsvParser();
        IEnumerable<string[]> csvHeaders;
        IEnumerable<string[]> csvRecords;
        String arrayItemLookupCodes = "";

        public InventoryForm()
        {
            InitializeComponent();
        }

        private void CreateDataGridViewHeader(DataGridView dgv, String textLine, bool fIsHeader)
        {
            csvHeaders = csvHeaderParser.GetCsvRecords(textLine, false);
            foreach (string[] strArr in csvHeaders)
            {
                for (int i = 0; i < strArr.Length; i++)
                {
                    if (fIsHeader)
                    {
                        dgv.Columns.Add(strArr[i], strArr[i]);
                    }
                    else
                    {
                        dgv.Columns.Add(i.ToString(), i.ToString());
                    }
                }
            }
        }

        private void openToolStripMenuItem_Click(object sender, EventArgs e)
        {
            int size = -1;
            //String inventoryFilePath = Environment.GetFolderPath(Environment.SpecialFolder.MyDocuments) + "\\ART_Inventory.csv";
            String inventoryFilePath = "C:\\ARSHelp\\ART_Inventory.csv";
            DialogResult result = openFileDialog1.ShowDialog(); // Show the dialog.
            int itemLookupCodeIndex = 2; // UpcCode
            int qtyReceivedIndex = 7; // Ship Qty
            int priceIndex = 10; // Cost

            if (result == DialogResult.OK) // Test result.
            {
                String file = openFileDialog1.FileName;
                try
                {
                    String text = File.ReadAllText(file);
                    size = text.Length;

                    dgCsvFile.Columns.Clear();
                    // Create the header row
                    string firstline = text.Substring(0, text.IndexOf("\n"));
                    CreateDataGridViewHeader(dgCsvFile, firstline, chkFirstRowHeader.Checked);
                    using (StreamWriter outputFile = new StreamWriter(inventoryFilePath)) // overwrite mode
                    {
                        outputFile.Write("");
                    }

                    csvRecords = csvParser.GetCsvRecords(text, chkFirstRowHeader.Checked);
                    toolStripStatusLabel1.Text = "Read " + csvRecords.Count().ToString() + " lines from file " + file + ".....";
                    statusStrip1.Update();
                    DataGridViewRow dgvr = null;
                    int i = -1;
                    foreach (string[] strArr in csvRecords)
                    {
                        i++;
                        dgvr = new DataGridViewRow();
                        dgvr.CreateCells(dgCsvFile, strArr);
                        dgCsvFile.Rows.Add(dgvr);
                        decimal qtyReceived = 0.0M;
                        if (!Decimal.TryParse(strArr[qtyReceivedIndex], out qtyReceived))
                        {
                            MessageBox.Show(String.Format("Failed to parse Qty Recieved for {0}, {1}, {3}", i, strArr[itemLookupCodeIndex], strArr[qtyReceivedIndex]));
                        }
                        else if (qtyReceived > 0.0M)
                        {
                            using (StreamWriter outputFile = new StreamWriter(inventoryFilePath, true)) // append mode
                            {
                                outputFile.WriteLine(strArr[itemLookupCodeIndex] + "," + strArr[qtyReceivedIndex]
                                    + "," + strArr[priceIndex].Replace("$", "").Replace(" ", "")
                                    // + "," + String.Format("{0:MM/dd/yyyy HH:mm:ss}", DateTime.Now)
                                    );
                            }
                            if (arrayItemLookupCodes.Length > 0)
                            {
                                arrayItemLookupCodes += ",";
                            }
                            arrayItemLookupCodes += "'" + strArr[itemLookupCodeIndex] + "'";
                        }
                    }
                    dgCsvFile.Show();
 
                    // Send Keystrokes to SOM
                    // https://msdn.microsoft.com/en-us/library/ms171548.aspx
                }
                catch (IOException iox)
                {
                    toolStripStatusLabel1.Text = "Exception " + iox.ToString() + " occurred while reading file " + file + ".....";
                    statusStrip1.Update();
                }
            }
        }

        private void exitToolStripMenuItem_Click(object sender, EventArgs e)
        {
            Application.Exit();
        }

        private void chkRollback_CheckedChanged(object sender, EventArgs e)
        {

        }

        private void storeToolStripMenuItem_Click(object sender, EventArgs e)
        {
            MessageBox.Show("TODO: Launch or switch to SOM.", "ART Tools");
        }

        private void settingsToolStripMenuItem_Click(object sender, EventArgs e)
        {
            MessageBox.Show("TODO: Display the settings page.", "ART Tools");
        }

        private void aboutToolStripMenuItem_Click(object sender, EventArgs e)
        {
            MessageBox.Show("Welcome to ART Invetory Application Version 0.1\nTested with RMS 2.0", "ART Help");
        }

        private void InventoryForm_Load(object sender, EventArgs e)
        {

        }

        private void btnView_Click(object sender, EventArgs e)
        {

        }

        private void btnCommit_Click(object sender, EventArgs e)
        {

        }
    }
}
