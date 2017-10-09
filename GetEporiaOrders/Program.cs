using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.IO;
using System.Text;
using System.Threading.Tasks;

namespace GetEporiaOrders
{
    class Program
    {
        static string FindDetailsPageLink(string searchContent, int startIndex, out int endIndex)
        {
            string href = "href";
            string url = null;
            endIndex = -1;
            int hrefIndex = searchContent.IndexOf(href, startIndex);
            if (hrefIndex >= 0)
            {
                endIndex = searchContent.IndexOf("\"", hrefIndex + 6);
                url = searchContent.Substring(hrefIndex + 6, endIndex - (hrefIndex + 6));
            }
            return url;
        }

        static String ExtractImage(String SKU)
        {
            String retVal = null;
            String SearchURLPattern = "http://www.americanretailsupply.com/Search.aspx?q={0}&f=&SearchPageNum=1";
            string websitePrefix = "http://www.americanretailsupply.com";

            String SKUSearchURL = String.Format(SearchURLPattern, SKU);
            Console.WriteLine(SKUSearchURL);

            WebRequest request = WebRequest.Create(SKUSearchURL);
            HttpWebResponse response = (HttpWebResponse)request.GetResponse();

            if (response.StatusCode == HttpStatusCode.OK)
            {
                Stream dataStream = response.GetResponseStream();
                StreamReader reader = new StreamReader(dataStream);

                string responseFromServer = reader.ReadToEnd();
                //Console.WriteLine(responseFromServer);

                string startSearchString = "class=\"bodytext\"";
                int startSearchStringIndex = responseFromServer.IndexOf(startSearchString);

                string endSearchString = "</table></div>";
                int endSearchStringIndex = responseFromServer.IndexOf(endSearchString, startSearchStringIndex);

                string relevantContent = responseFromServer.Substring(startSearchStringIndex, (endSearchStringIndex - startSearchStringIndex));
                //Console.WriteLine(relevantContent);

                int newStartIndex = 0;

                do
                {
                    int startindex = newStartIndex;
                    string url = FindDetailsPageLink(relevantContent, startindex, out newStartIndex);

                    if (url == null)
                    {
                        break;
                    }

                    url = websitePrefix + url;
                    Console.WriteLine(url);

                    retVal = ExtractImageFromDetails(url, SKU);

                    if (retVal != null)
                    {
                        break;
                    }

                    startindex = newStartIndex;
                    FindDetailsPageLink(relevantContent, startindex, out newStartIndex);
                } while(true);
                    
                reader.Close();
                dataStream.Close();
            }
            response.Close();

            return retVal;
        }

        static string GetCommonImage(string content)
        {
            string retval = null;
            string commonImageSize = "300x600";
            int commonImageSizeIndex = content.IndexOf(commonImageSize);
            if (commonImageSizeIndex > 0)
            {
                int commonImageSizeUrlIndex = content.LastIndexOf("<img src=", commonImageSizeIndex);
                if (commonImageSizeUrlIndex > 0)
                {
                    int commonImageSizeEndIndex = content.IndexOf("\"", (commonImageSizeUrlIndex + 10));
                    retval = content.Substring((commonImageSizeUrlIndex + 10), (commonImageSizeEndIndex - (commonImageSizeUrlIndex + 10)));
                }
            }
            return retval;
        }

        static string ExtractImageFromDetails(string url, string SKU)
        {
            String retVal = null;
            String SKUSearchURL = String.Format(url, SKU);
            Console.WriteLine(SKUSearchURL);

            WebRequest request = WebRequest.Create(SKUSearchURL);
            HttpWebResponse response = (HttpWebResponse)request.GetResponse();

            if (response.StatusCode == HttpStatusCode.OK)
            {
                Stream dataStream = response.GetResponseStream();
                StreamReader reader = new StreamReader(dataStream);

                string responseFromServer = reader.ReadToEnd();
                //Console.WriteLine(responseFromServer);

                int SKUIndex = responseFromServer.IndexOf(">" + SKU + "<");

                if (SKUIndex >= 0)
                {
                    string noImageColumnName = "<td class=\"first text-left\">";
                    int noImageColumnIndex = responseFromServer.LastIndexOf(noImageColumnName, SKUIndex);

                    if (noImageColumnIndex >= 0)
                    {
                        // Get common image
                        retVal = GetCommonImage(responseFromServer);                      
                    }
                    else
                    {
                        string imageColumnName = "<td class=\"first\">";
                        int imageColumnIndex = responseFromServer.LastIndexOf(imageColumnName, SKUIndex);

                        if (imageColumnIndex >= 0)
                        {
                            string relevantContent = responseFromServer.Substring(imageColumnIndex, (SKUIndex - imageColumnIndex));

                            int imageURLIndex = relevantContent.IndexOf("<img src=");

                            if (imageURLIndex >= 0)
                            {
                                // Get the image from column
                                int endImageIndex = relevantContent.IndexOf("\"", (imageURLIndex + 10));

                                retVal = relevantContent.Substring((imageURLIndex + 10), endImageIndex - (imageURLIndex + 10));
                            }
                            else
                            {
                                // Get common image
                                retVal = GetCommonImage(responseFromServer);
                            }
                        }

                    }
                }

                
                reader.Close();
                dataStream.Close();
            }
            response.Close();
            return retVal;
        }

        static void Main(string[] args)
        {
            if (args.Length > 0)
            {
                Console.WriteLine("Processing file: " + args[0]);
            }
            else
            {
                Console.WriteLine("Invalid input. Please pass file path as argument to program.");
                return;
            }

            string[] skuArray = null;
            try
            {
                // Read all lines into a string Array
                skuArray = File.ReadAllLines(args[0]);
                
                // Erase outfile file in write mode and write headers
                using (StreamWriter outputFile = new StreamWriter(args[0] + ".xls"))
                {
                    outputFile.WriteLine("SKU\tEporiaImageLink");
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("Exception: " + ex.Message + " occurred during initialization.");
                return;
            }

            // Read each SKU string from the string array
            foreach (string SKU in skuArray)
            {
                Console.WriteLine("Processing SKU - " + SKU);
                string output = SKU + "\t";

                try
                {
                    string imageUrl = ExtractImage(SKU);

                    if (imageUrl != null)
                    {
                        output = output + imageUrl;
                        Console.WriteLine(imageUrl);
                    }
                }
                catch (Exception)
                {
                    output = output + "FAIL";
                    Console.WriteLine("Failed to process SKU - " + SKU);
                }

                // append link and close file
                using (StreamWriter outputFile = new StreamWriter(args[0] + ".xls", true))
                {
                    outputFile.WriteLine(output);
                }
            }

            Console.WriteLine("Processing complete. Press any key to exit.");
            Console.ReadLine();
        }
    }
}
