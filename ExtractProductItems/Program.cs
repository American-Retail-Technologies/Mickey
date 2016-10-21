using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.IO;
using System.Text;
using System.Threading.Tasks;

namespace ExtractProductItems
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

        static string ExtractCategory(string searchContent, int startIndex, out int endIndex)
        {
            string bcrumbs = "<div id=\"bcrumbs\">";
            string classLink = "class=\"link\">";
            int lenClassLink = classLink.Length;

            string category = null;
            string subCategory = "";
            endIndex = -1;
            int tempIndex = searchContent.IndexOf(bcrumbs, startIndex);
            if (tempIndex >= 0)
            {
                category = "Default Category";
                endIndex = searchContent.IndexOf("</span></div>", tempIndex);
                int productNameStartIndex = searchContent.IndexOf("<span class=\"active\">", tempIndex);
                while (tempIndex < productNameStartIndex - 7)
                {
                    startIndex = searchContent.IndexOf(classLink, tempIndex) + lenClassLink;
                    tempIndex = searchContent.IndexOf("</a>", startIndex);
                    subCategory = searchContent.Substring(startIndex, tempIndex - startIndex);
                    // Replace / with space
                    subCategory = subCategory.Replace("/", " ");
                    // Remove Leading and trailing spaces
                    subCategory = subCategory.Trim();
                    category += "/" + subCategory;
                }
                // Append product name now
                startIndex = productNameStartIndex + "<span class=\"active\">".Length;
                category += "/" + searchContent.Substring(startIndex, endIndex - startIndex);
                // Remove Home/
                category = category.Replace("Home/", "");
                // Replace , with ;
                category = category.Replace(",", ";");
                // Replace triple spaces with single space;
                category = category.Replace("   ", " ");
                // Replace double spaces with single space;
                category = category.Replace("  ", " ");
            }
            return category;
        }

        static string ExtractProductDescription(string searchContent, int startIndex, out int endIndex)
        {
            string description = null;
            endIndex = -1;
            int descriptionIndex = searchContent.IndexOf("<div class=\"Content \" >", startIndex) + "<div class=\"Content \" >".Length;
            if (descriptionIndex >= 0)
            {
                endIndex = searchContent.IndexOf("</div></td>", descriptionIndex);
                description = searchContent.Substring(descriptionIndex, endIndex - descriptionIndex);
            }
            return description;
        }


        static string ExtractProductImageUrl(string searchContent, int startIndex, out int endIndex)
        {
            string imageUrl = null;
            endIndex = -1;
            string strToFind = "<div id=\"ctl00_Main_PrimaryImagePanel\">";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                strToFind = "<img src=";
                tempIndex = searchContent.IndexOf(strToFind, tempIndex);
                // now find the index of /img
                startIndex = searchContent.IndexOf("/img", tempIndex);
                // find ?
                endIndex = searchContent.IndexOf("?", startIndex);
                imageUrl = searchContent.Substring(startIndex, endIndex - startIndex);
            }
            return imageUrl;
        }

        static void Main(string[] args)
        {
            string strFolder = "D:\\ARS\\ARS_Web_3\\";
            if (args.Length > 0)
            {
                if (args.Length > 2)
                {
                    strFolder = args[1];
                }
                Console.WriteLine("Processing file: " + args[0] + " from folder " + strFolder);
            }
            else
            {
                Console.WriteLine("Invalid input. Please pass file and path as argument to program.");
                return;
            }

            string[] productFileNames = null;
            try
            {
                // Read all lines into a string Array
                productFileNames = File.ReadAllLines(args[0]);
                
                // Erase outfile file in write mode and write headers
                using (StreamWriter outputFile = new StreamWriter(args[0] + ".csv"))
                {
                    outputFile.Write("sku, categories, description, base_image");

                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("Exception: " + ex.Message + " occurred during initialization.");
                return;
            }

            // Read each fileName string from the string array
            foreach (string fileName in productFileNames)
            {
                Console.WriteLine("Processing fileName - " + fileName);
                string output = "";
                string fileContents = "";
                string strFilePath = strFolder + fileName;
                // Read the file as text
                using (StreamReader inputFile = new StreamReader(strFilePath))
                {
                    fileContents = inputFile.ReadToEnd();
                }

                int lastPointer = 0;

                try
                {
                    string productCategory = ExtractCategory(fileContents, lastPointer, out lastPointer);
                    if (productCategory == null)
                    {
                        productCategory = "Default Category/Unknown Category";
                    }
                    string productDescription = ExtractProductDescription(fileContents, lastPointer, out lastPointer);
                    if (productDescription == null)
                    {
                        productDescription = "";
                    }

                    string productImageUrl = ExtractProductImageUrl(fileContents, lastPointer, out lastPointer);
                    if (productImageUrl != null)
                    {
                        productImageUrl = "http://americanretailsupply.net/pub/product_images" + productImageUrl.Replace(".eetoolset.com", "");
                    }
                    else
                    {
                        productImageUrl = "";
                    }

                    // Move the pointer to Find Product Variant lines
                    lastPointer = fileContents.IndexOf("<div id=\"ctl00_Main_TablePanel\">", lastPointer);

                    string sku = "0";

                    output += String.Format("\n{0},\"{1}\",\"{2}\",\"{3}\"",
                        sku, productCategory.Replace("\"", "\"\""), 
                        productDescription.Replace("\"", "\"\""), 
                        productImageUrl);

                    Console.WriteLine(output);
                }
                catch (Exception)
                {
                    output = output + "FAIL";
                    Console.WriteLine("Failed to process fileName - " + fileName);
                }

                // append link and close file
                using (StreamWriter outputFile = new StreamWriter(args[0] + ".csv", true))
                {
                    outputFile.WriteLine(output);
                }
            }

            Console.WriteLine("Processing complete. Press any key to exit.");
            Console.ReadLine();
        }
    }
}
