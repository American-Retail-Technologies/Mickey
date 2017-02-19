using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Windows.Forms;

namespace RmsInventoryImport
{
    public partial class InventoryForm : Form
    {
        CsvParser csvParser = new CsvParser();
        CsvParser csvHeaderParser = new CsvParser();
        IEnumerable<string[]> csvHeaders;
        IEnumerable<string[]> csvRecords;

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
            DialogResult result = openFileDialog1.ShowDialog(); // Show the dialog.
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

                    csvRecords = csvParser.GetCsvRecords(text, chkFirstRowHeader.Checked);
                    toolStripStatusLabel1.Text = "Read " + csvRecords.Count().ToString() + " lines from file " + file + ".....";
                    statusStrip1.Update();
                    DataGridViewRow dgvr = null;
                    foreach (string[] strArr in csvRecords)
                    {
                        dgvr = new DataGridViewRow();
                        dgvr.CreateCells(dgCsvFile, strArr);
                        dgCsvFile.Rows.Add(dgvr);
                    }
                    dgCsvFile.Show();
                    // Trasform the file...

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

        private void btnImport_Click(object sender, EventArgs e)
        {

        }

        private void btnRollback_Click(object sender, EventArgs e)
        {

        }
    }
}
