using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.IO;
using System.Text;
using System.Threading.Tasks;
using System.Net.Cache;

namespace ExtractProductItems
{
    class Program
    {
        // This dictionary is used to check if the SKU was already added.
        private static Dictionary<string, int> skuDictionary = new Dictionary<string, int>();
        // Set one of these flags to true to downoad the latest content
        //private static bool fUpdateProductPageCache = false; // TODO: Implement later to get latest file, if required.
        private static bool fUpdateItemImageCache = false;
        private static bool fUpdateSwatchImageCache = false;
        private static bool fUpdateSwatchContentCache = false;

        // Based on http://www.codeproject.com/Articles/13503/Stripping-Accents-from-Latin-Characters-A-Foray-in
        private static string LatinToAscii(string inString)
        {
            var newStringBuilder = new StringBuilder();
            newStringBuilder.Append(inString.Normalize(NormalizationForm.FormKD)
                                            .Where(x => x < 128)
                                            .ToArray());
            return newStringBuilder.ToString();
        }

        // http://stackoverflow.com/questions/1038431/how-to-clean-html-tags-using-c-sharp
        // Remove tags other than the <a> and </a> tags and <br>
        public static string RemoveTags(string html)
        {
            string returnStr = "";
            bool insideTag = false;
            int htmlLength = html.Length;
            for (int i = 0; i < htmlLength; ++i)
            {
                char c = html[i];
                if (c == '<')
                {
                    insideTag = true;
                    if (html[i + 1] == 'a')
                    {
                        insideTag = false;
                    }
                    else if (i < htmlLength - 3 &&
                        Char.ToUpper(html[i + 1]) == 'B' &&
                        Char.ToUpper(html[i + 2]) == 'R')
                    {
                        insideTag = false;
                    }
                }

                if (!insideTag) returnStr += c;

                if (c == '>') insideTag = false;
            }
            return returnStr;
        }

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

                            
        static bool ExtractMetaTags(string searchContent, out string metaTitle, out string metaKeywords, out string metaDescription, string strInputFilePath)
        {
            bool fRet = true;
            metaTitle = "";
            metaKeywords = "";
            metaDescription = "";
            int startIndex = 0;
            int endIndex = -1;
            string strToFind = "<head><title>";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                startIndex = tempIndex + strToFind.Length;
                strToFind = "</title>";
                endIndex = searchContent.IndexOf(strToFind, startIndex);
                if (endIndex > startIndex)
                {
                    metaTitle = searchContent.Substring(startIndex, endIndex - startIndex).Trim();
                    metaTitle = LatinToAscii(metaTitle);
                }
            }
            strToFind = "<meta name=\"keywords\" content=\"";
            tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                startIndex = tempIndex + strToFind.Length;
                strToFind = "\"";
                endIndex = searchContent.IndexOf(strToFind, startIndex);
                if (endIndex > startIndex)
                {
                    metaKeywords = searchContent.Substring(startIndex, endIndex - startIndex).Trim();
                    metaKeywords = LatinToAscii(metaKeywords);
                }
            }
            strToFind = "<meta name=\"description\" content=\"";
            tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                startIndex = tempIndex + strToFind.Length;
                strToFind = "\"";
                endIndex = searchContent.IndexOf(strToFind, startIndex);
                if (endIndex > startIndex)
                {
                    metaDescription = searchContent.Substring(startIndex, endIndex - startIndex).Trim();
                    metaDescription = LatinToAscii(metaDescription);
                }
            }

            return fRet;
        }

        static string ExtractSubCategoryTable(string searchContent, int startIndex, string strInputFilePath)
        {
            string subCategoryTable = null;
            string strToFind = "<table class=";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                startIndex = tempIndex;
                strToFind = "</table>";
                tempIndex = searchContent.IndexOf(strToFind, startIndex);
                if (tempIndex > startIndex)
                {
                    tempIndex += strToFind.Length;
                    subCategoryTable = searchContent.Substring(startIndex, tempIndex - startIndex).Trim();
                }
            }

            return subCategoryTable;
        }

        static string ExtractProductHierarchy(string searchContent, int startIndex, out int endIndex, out string categoryName, string strInputFilePath)
        {
            string bcrumbs = "<div id=\"bcrumbs\">";
            string classLink = "class=\"link\">";
            int lenClassLink = classLink.Length;

            string category = null;
            string subCategory = "";
            int productNameStartIndex = 0;
            int tempIndex = 0;
            endIndex = -1;
            categoryName = "";
            // Allocate to Missing Category if bcrumbs is empty: see www.americanretailsupply.com\10264\1533\Avery-Dennison-One-Line-Price-Gun\PB-1-Labels.html
            if ((endIndex = searchContent.IndexOf("<div id=\"bcrumbs\"></div>")) != -1)
            {
                Console.WriteLine("***WARNING***: Found BLANK BREAD CRUMB file: " + strInputFilePath);
                category = "Default Category/Missing Category";
                productNameStartIndex = searchContent.IndexOf("<tr><td class=\"textcol\"><h1>", endIndex);
                productNameStartIndex += "<tr><td class=\"textcol\"><h1>".Length;
                tempIndex = searchContent.IndexOf("</h1>", productNameStartIndex); ; // used for Product Name
                categoryName = "Missing Category";
            }
            else
            {
                tempIndex = searchContent.IndexOf(bcrumbs, startIndex);
                if (tempIndex >= 0)
                {
                    category = "Default Category";
                    endIndex = searchContent.IndexOf("</span></div>", tempIndex);
                    productNameStartIndex = searchContent.IndexOf("<span class=\"active\">", tempIndex);
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
                    productNameStartIndex += "<span class=\"active\">".Length;
                    tempIndex = endIndex; // used for Product Name
                }
            }
            if (category != null)
            {
                // Append product name now
                categoryName = searchContent.Substring(productNameStartIndex, tempIndex - productNameStartIndex).Replace("/", " ").Trim();
                category += "/" + categoryName;
                // Remove Home/
                category = category.Replace("Home/", "");
                // Replace , with ;
                category = category.Replace(",", ";");
                // Remove | 
                category = category.Replace("|", "");
                // Replace triple spaces with single space;
                category = category.Replace("   ", " ");
                // Replace double spaces with single space;
                category = category.Replace("  ", " ");

            }
            return category;
        }

        static string ExtractProductDescription(string searchContent, int startIndex, ref int endIndex)
        {
            string description = null;
            int descriptionIndex = searchContent.IndexOf("<div class=\"Content \" >", startIndex);
            if (descriptionIndex <= 0)
            {
                return null;
            }
            descriptionIndex += "<div class=\"Content \" >".Length;
            endIndex = searchContent.IndexOf("</div>", descriptionIndex);
            description = searchContent.Substring(descriptionIndex, endIndex - descriptionIndex);
            // Fix the links in the descirption
            description = description.Replace("../../../../rs6.eporia.com", "http://yoosh.co/ars_files/rs6.eporia.com");
            description = description.Replace("../../../../resources.myeporia.com", "http://yoosh.co/ars_files/resources.myeporia.com");
            description = description.Replace("../../../../is1.eporia.com", "http://yoosh.co/ars_files/is1.eporia.com");
            description = description.Replace("../../../../is7.eporia.com", "http://yoosh.co/ars_files/is7.eporia.com");
            description = description.Replace("../../../../is10.eporia.com", "http://yoosh.co/ars_files/is10.eporia.com");
            description = description.Replace("../../../../is30.eporia.com", "http://yoosh.co/ars_files/is30.eporia.com");
            description = description.Replace("../../../../ais.eporia.com", "http://yoosh.co/ars_files/ais.eporia.com");
            // This was found in 3 categories:
            description = description.Replace("../../is7.eporia.com", "http://yoosh.co/ars_files/is7.eporia.com");
            description = LatinToAscii(description);
            description = description.Replace("&nbsp;", " ");
            description = "<p>" + RemoveTags(description) + "</p>";
            return description;
        }

        // TODO: Return the URL from the image server also.
        static string ExtractProductImageUrl(string searchContent, int startIndex, out int endIndex)
        {
            string imageUrl = null;
            endIndex = -1;
            string strToFind = "<div id=\"ctl00_Main_PrimaryImagePanel\">";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                strToFind = "<img src=";
                endIndex = searchContent.IndexOf("</div>", tempIndex);
                tempIndex = searchContent.IndexOf(strToFind, tempIndex);
                // 02/11/2017: In som rare cases product image can be blank. return null in those cases
                if (endIndex < tempIndex)
                {
                    return null;
                }
                // now find the index of /img
                startIndex = searchContent.IndexOf("/img", tempIndex);
                // find ?
                endIndex = searchContent.IndexOf("?", startIndex);
                imageUrl = searchContent.Substring(startIndex, endIndex - startIndex);
            }
            return imageUrl;
        }

        static string ExtractProductNameAndText(string searchContent, int startIndex, out int endIndex, out string productText)
        {
            string productName = null;
            productText = null;
            endIndex = -1;
            string strToFind = "<span class=\"name\">";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                strToFind = "</span>";
                endIndex = searchContent.IndexOf("</span>", tempIndex);
                productName = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                strToFind = "<span class=\"text\">";
                tempIndex = searchContent.IndexOf(strToFind, endIndex);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    strToFind = "</span>";
                    endIndex = searchContent.IndexOf("</span>", tempIndex);
                    productText = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                }
            }
            return productName;
        }

        static string ExtractSwatchName2AndText(string searchContent, int startIndex, out string swatchUnits, string otherSwatchRowName)
        {
            string swatchName2 = null;
            swatchUnits = null;
            int endIndex = -1;
            string strToFind = "<span class=\"name\">";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                strToFind = "</span>";
                endIndex = searchContent.IndexOf("</span>", tempIndex);
                swatchName2 = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                strToFind = "<div class=\"units\">";
                tempIndex = searchContent.IndexOf(strToFind, endIndex);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    strToFind = "</div>";
                    endIndex = searchContent.IndexOf(strToFind, tempIndex);
                    swatchUnits = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                }
            }
            return swatchName2;
        }

        static string ExtractItemsHeaderRow(string searchContent, int startIndex, out int endIndex, out ProductHeaderType headerType)
        {
            string headerRow = null;
            endIndex = -1;
            headerType = ProductHeaderType.Unknown;
            string strToFind = "<tr class=\"top\">";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                strToFind = "</tr>";
                endIndex = searchContent.IndexOf("</tr>", tempIndex);
                headerRow = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                headerType = GetHeaderRowType(headerRow);
            }
            return headerRow;
        }

        static string ExtractNextItemRow(string searchContent, int startIndex, out int endIndex, ProductHeaderType headerType)
        {
            string itemRow = null;
            endIndex = -1;
            // Find next mid OR mid2 and use that.....
            int tempIndex = -1;
            int indexMid2 = searchContent.IndexOf("<tr class=\"mid2\">", startIndex);
            int indexMid = searchContent.IndexOf("<tr class=\"mid\">", startIndex);

            // Pick the next one if both present
            if (indexMid2 > 0 && indexMid > 0)
            {
                tempIndex = (indexMid2 < indexMid) ? (indexMid2 + "<tr class=\"mid2\">".Length) : (indexMid + "<tr class=\"mid\">".Length);
            }
            else // Pick the one that is positive, otherwise leave tempIndex as -1
            {
                if (indexMid2 > 0) tempIndex = indexMid2;
                if (indexMid > 0) tempIndex = indexMid;
            }

            // mid or mid2 not found look for bot ot bot2        
            if (tempIndex < 0)
            {
                tempIndex = searchContent.IndexOf("<tr class=\"bot2\">", startIndex);
                if (tempIndex > 0) // Not -1
                {
                    tempIndex += "<tr class=\"bot2\">".Length;
                }
                else
                {
                    tempIndex = searchContent.IndexOf("<tr class=\"bot\">", startIndex);
                    if (tempIndex > 0) // Not -1
                    {
                        tempIndex += "<tr class=\"bot\">".Length;
                    }
                }
            }
            if (tempIndex >= 0)
            {
                endIndex = searchContent.IndexOf("</tr>", tempIndex);
                itemRow = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
            }
            return itemRow;
        }

        static string ExtractSwatchImageUrl(string searchContent, ref int lastPointer, ref string swatchImageDownloadUrl)
        {
            string imageUrl = null;
            int endIndex = -1;
            string strToFind = "class=\"swatch\" src=\"";
            swatchImageDownloadUrl = null;
            int tempIndex = searchContent.IndexOf(strToFind, lastPointer);
            if (tempIndex >= 0)
            {
                // now find the index of /img
                tempIndex = searchContent.IndexOf("/img", tempIndex);
                // find ?
                endIndex = searchContent.IndexOf("?", tempIndex);
                imageUrl = searchContent.Substring(tempIndex, endIndex - tempIndex);
                // Get the swatchImageDownloadUrl also....
                endIndex = searchContent.IndexOf("/img", tempIndex + 4);
                swatchImageDownloadUrl = "http:/" + searchContent.Substring(tempIndex, endIndex - tempIndex) + "/img";
                tempIndex = searchContent.IndexOf("?", tempIndex);
                endIndex = searchContent.IndexOf(",size[", tempIndex);
                swatchImageDownloadUrl += searchContent.Substring(tempIndex, endIndex - tempIndex) + ",size[300x300],qual[80]&call=url[file:std.image]";
                lastPointer = endIndex;
            }
            return imageUrl;
        }

        static string ExtractSwatchNameFromControl(string searchContent, ref int lastPointer)
        {
            string swatchName = null;
            int endIndex = -1;
            string strToFind = "<p class=\"swatch-text\">";
            int tempIndex = searchContent.IndexOf(strToFind, lastPointer);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                endIndex = searchContent.IndexOf("</p>", tempIndex);
                swatchName = searchContent.Substring(tempIndex, endIndex - tempIndex);
                lastPointer = endIndex;
            }
            return swatchName;
        }

        static string ExtractNextSwatch(string searchContent, ref int lastPointer, ref string swatchImageUrl, ref string swatchName, ref string swatchImageDownloadUrl)
        {
            string swatchControlId = null;
            swatchImageDownloadUrl = null;
            // Find id=\"ctl00_Main_Swatch, then find ending quote
            string strToFind = "id=\"ctl00_Main_Swatch";
            int tempIndex = searchContent.IndexOf(strToFind, lastPointer);
            if (tempIndex >= 0)
            {
                tempIndex += 4; // id="
                lastPointer = searchContent.IndexOf("\"", tempIndex);
                swatchControlId = searchContent.Substring(tempIndex, lastPointer - tempIndex).Trim();
            }
            if (swatchControlId != null)
            {
                swatchImageUrl = ExtractSwatchImageUrl(searchContent, ref lastPointer, ref swatchImageDownloadUrl);
                swatchName = ExtractSwatchNameFromControl(searchContent, ref lastPointer);
            }
            return swatchControlId;
        }

        enum ProductHeaderType
        {
            Unknown,
            SKU_1,
            SKU_Arms_Description_1,
            SKU_Available_1,
            SKU_Category_Color_1,
            SKU_Category_Dimensions_Color_1,
            SKU_Category_Sizes_1,
            SKU_Color_1,
            SKU_Color_2,
            SKU_Color_4,
            SKU_Color_Description_1,
            SKU_Color_Print_Size_2,
            SKU_Color_Size_1,
            SKU_Connectors_1,
            SKU_Description_1,
            SKU_Description_2,
            SKU_DescriptionHxWxL_1,
            SKU_DescriptionHxLeg_1,
            SKU_DescriptionHxDiameter_1,
            SKU_DescriptionHxWxD_1,
            SKU_DescriptionLxWxH_1,
            SKU_DescriptionLxW_1,
            SKU_DescriptionLxWxFront_1,
            SKU_DescriptionWxD_1,
            SKU_DescriptionWxLxH_1,
            SKU_DescriptionWxH_1,
            SKU_DescriptionWxHxD_1,
            SKU_DescriptionWxDxH_1,
            SKU_DescriptionsWxDxH_1,
            SKU_DescriptionsWxH_1,
            SKU_Description_Color_1,
            SKU_Description_Color_2,
            SKU_Description_Connectors_1,
            SKU_Description_Dimensions_1,
            SKU_Description_Finish_1,
            SKU_Description_Lights_1,
            SKU_Description_PacksPerCase_1,
            SKU_Description_Print_1,
            SKU_Description_Size_1,
            SKU_Description_Size_Color_1,
            SKU_Description_String_1,
            SKU_Description_TicketSize_1,
            SKU_Diameter_1,
            SKU_Diameter_Length_1,
            SKU_Dimensions_1,
            SKU_DimensionsLxWxH_1,
            SKU_Dimensions_Color_1,
            SKU_Dimensions_Description_1,
            SKU_Finish_1,
            SKU_Finish_Description_1,
            SKU_Finish_Lights_1,
            SKU_Finish_Size_1,
            SKU_Glass_Connectors_1,
            SKU_Item_1,
            SKU_Length_1,
            SKU_Length_Color_1,
            SKU_Material_1,
            SKU_Options_1,
            SKU_Print_1,
            SKU_Print_Size_1,
            SKU_Quantity_1,
            SKU_Size_1,
            SKU_SizeWxDxH_1,
            SKU_Size_Color_1,
            SKU_Size_Color_2,
            SKU_Size_Description_1,
            SKU_Size_Description_Color_1,
            SKU_Size_Finish_Lights_1,
            SKU_Size_NumberOfPockets_1,
            SKU_Size_Print_1,
            SKU_Size_StrungUnStrung_1,
            SKU_Style_1,
            SKU_Style_Color_1,
            SKU_Style_Finish_Lights_1,
            SKU_TicketSize_1,
            SKU_Type_Color_1,
            SKU_Type_Description_1,
            SKU_Type_DescriptionLxW_1,
            SKU_Type_DescriptionLxWxFront_1,
            SKU_Width_Finish_1,
            SKU_Width_Finish_Lights_1,
            SKU_WireThickness_Length_Color_1,
            CustomerService_Resources
        }

        static ProductHeaderType GetHeaderRowType(string headerRow)
        {

            ProductHeaderType headerType = ProductHeaderType.Unknown;
            if (headerRow.Contains("rowspan=\"2\">SKU</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td>2+</td>"))
            {
                headerType = ProductHeaderType.SKU_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxH)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (W x H)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Descriptions (WxH)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionsWxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Descriptions (WxH)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionsWxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxDxH)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxDxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Descriptions (HxLxW)</td><td rowspan=\"2\">Color</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Color</td><td>2+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxDxH)</td><td rowspan=\"2\">Color</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxDxH)</td><td rowspan=\"2\">Color</td><td>2+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (LxW)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionLxW_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (HxLeg)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionHxLeg_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (H x Diameter)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionHxDiameter_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (LxWxH)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionLxWxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (H x W x L)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionHxWxL_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (W x H x D)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxHxD_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxHxD)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxHxD_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (W x D x H)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxDxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description ( W x D x H ) </td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxDxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (H x W x D)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionHxWxD_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description  (H x W x D)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionHxWxD_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (HxWxD)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionHxWxD_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (H x W x L)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_DescriptionHxWxL_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td>5+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td>2+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_2;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Decription</td><td>2+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_2;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Decription</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description </td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Descripton</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Size</td><td>4+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Description</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Size_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Print</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Size_Print_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Diameter</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Diameter_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Diameter</td><td rowspan=\"2\">Length</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Diameter_Length_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Finish</td><td rowspan=\"2\">Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Finish_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Lights</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Laminates/Finishes</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Finish_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Light</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Lighting</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Width</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Lights</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Width_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Width</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Light</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Width_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Width</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Lighting</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Width_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Light</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Size_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Style</td><td rowspan=\"2\">Laminates/Finishes</td><td rowspan=\"2\">Lights</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Style_Finish_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Arms</td><td rowspan=\"2\">Description</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Arms_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Available</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Available_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Connectors</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Connectors_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Length</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Length_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Material</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Material_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Options</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Options_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Print</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Print_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Print</td><td rowspan=\"2\">Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Print_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Packs per Case</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_PacksPerCase_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Ticket Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_TicketSize_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Finish</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Finish_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Connectors</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Connectors_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Dimensions</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Dimensions_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Dim-X</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Dimensions_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Lighting</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Lights</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (H x W x L)</td><td rowspan=\"2\">Lighting</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Lights_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">String</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_String_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Glass</td><td rowspan=\"2\">Connectors</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Glass_Connectors_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxLxH)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxLxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Dimensions (L x W x H)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_DimensionsLxWxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Print</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Print_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Category</td><td rowspan=\"2\">Dimensions</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Category_Dimensions_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Category</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Category_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Category</td><td rowspan=\"2\">Sizes</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Category_Sizes_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Style</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Style_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Dimensions</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Dimensions_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Dimensions</td><td rowspan=\"2\">Description</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Dimensions_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Dimensions</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Dimensions_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Quantity</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Quantity_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Length</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Length_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Colors</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>2+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_2;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>4+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_4;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>5+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td rowspan=\"2\">Description</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td rowspan=\"2\">Description</td><td>2+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td rowspan=\"2\">Description</td><td>5+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Item</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Item_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size (WxDxH)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_SizeWxDxH_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Sizes</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size (LxWxFront)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size (WxH)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Box Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Strung/Unstrung</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_StrungUnStrung_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size </td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size  (W x L x H)</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Color</td><td>2+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_Color_2;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Description</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Number of Pockets</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_NumberOfPockets_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Style</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Style_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Ticket Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_TicketSize_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Type</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Type_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Wire Thickness</td><td rowspan=\"2\">Length</td><td rowspan=\"2\">Color</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_WireThickness_Length_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Type</td><td rowspan=\"2\">Description</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Type_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Type</td><td rowspan=\"2\">Description </td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Type_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td>4+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td>6+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Descriptiion</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Descriptions</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Type</td><td rowspan=\"2\">Description (LxW)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Type_DescriptionLxW_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Type</td><td rowspan=\"2\">Description (LxWxFront)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Type_DescriptionLxWxFront_1;
            }
            // Size is more attribute than Type, that's why mapping to SKU_Description_Size_1.
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Type</td><td rowspan=\"2\">Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (LxWxFront)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_DescriptionLxWxFront_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxD)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxD_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxHxD)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_DescriptionWxHxD_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description  (WxH)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td rowspan=\"2\">Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td rowspan=\"2\">Size</td><td>4+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td rowspan=\"2\">size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">color</td><td rowspan=\"2\">Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Style/Color</td><td rowspan=\"2\">Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td rowspan=\"2\">Print</td><td rowspan=\"2\">Size</td><td>2+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_Print_Size_2;
            }
            else if (headerRow.Contains(">Customer Service</td><td>Resources</td>"))
            {
                headerType = ProductHeaderType.CustomerService_Resources;
            }
            // TODO: Found 99 New LINES with missing row header types. Mapping to existing type For NOW
            // REFACTOR These later
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Case Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Connectors_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Case Pack</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Connectors_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Designs</td><td rowspan=\"2\">Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Design</td><td rowspan=\"2\">Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Assorted</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Components </td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Case size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Numbers</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            // can thie be mapped to size or dimension
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">W x D x H</td><td>12+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            // can this be mapped color or style or size
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color/Style/Assorted Sizes</td><td>12+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Color/Style/Assorted Sizes</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            // can width be separate attribute or can it be mapped to size or dimension
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Width</td><td rowspan=\"2\">Laminates/Finishes</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Width_Finish_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">W x H</td><td rowspan=\"2\">Description</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_Description_1;
            }
            // what is in this column mapping to size for now
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">W x L + Bottom Gusset</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Size_1;
            }
            // see if thickness can be mapped to something else
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Thickness</td><td rowspan=\"2\">Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            // map description to diameter for now
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Length</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Diameter_Length_1;
            }
            // map back to description so use SKU_Description_String_1
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Back</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_String_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Item</td><td rowspan=\"2\">Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Kit</td><td rowspan=\"2\">Color</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Color_1;
            }
            // material could become an attribute?
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Material</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_String_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Pipe Color</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Size_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Height</td><td rowspan=\"2\">Pipe Color</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Size_Description_Color_1;
            }
            else if (headerRow.Contains("rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Roll Size</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.SKU_Description_Size_1;
            }
            else
            {
                Console.WriteLine("****Error**** UNKNOWN HEADER ROW: " + headerRow);
            }
            return headerType;
        }

        static string ExtractDescrpt(string searchContent, int startIndex, ref int endIndex)
        {
            string descrpt = null;
            string strToFind = "<td class=\"descrpt\">";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                endIndex = searchContent.IndexOf("</td>", tempIndex);
                descrpt = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
            }
            return descrpt;
        }

        static string ExtractColor(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_color=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractSize(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_size=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractDimensions(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_dimension=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractCategory(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_category=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractType(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_type=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractStyle(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_style=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractLength(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_length=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractPrint(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_print=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractFinish(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_finish=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractWidth(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_width=" + descrpt;
            }
            return descrpt;
        }

        static string ExtractLights(string searchContent, int startIndex, ref int endIndex, ref string attributes)
        {
            string descrpt = null;
            descrpt = ExtractDescrpt(searchContent, startIndex, ref endIndex);
            if (descrpt != null)
            {
                if (attributes.Length > 0)
                {
                    attributes += ",";
                }
                attributes += "ars_lights=" + descrpt;
            }
            return descrpt;
        }

        static bool GetItemData(string searchContent, ProductHeaderType headerType, out string itemSku, 
            ref string shortDescription, out string itemImageUrl, out string itemPrice, ref string attributes,  string strBaseFolder)
        {
            bool fRet = false;
            itemSku = null;
            itemImageUrl = null;
            itemPrice = null; // Item not found
            string itemImageDownloadUrl = null;

            int endIndex = -1;
            int tempIndex = -1;
            // If it contains "<td class=\"first\">" THEN there is an image
            string strToFind = "<td class=\"first\">";
            tempIndex = searchContent.IndexOf(strToFind);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                strToFind = "/img";
                tempIndex = searchContent.IndexOf(strToFind, tempIndex);
                if (tempIndex > 0) // Some rows have images while others don't
                {
                    endIndex = searchContent.IndexOf("?", tempIndex);
                    itemImageUrl = searchContent.Substring(tempIndex, endIndex - tempIndex).Replace(".eetoolset.com", "");
                    // Get the itemImageDownloadUrl also....
                    endIndex = searchContent.IndexOf("/img", tempIndex+4);
                    itemImageDownloadUrl = "http:/" + searchContent.Substring(tempIndex, endIndex - tempIndex) + "/img";
                    tempIndex = searchContent.IndexOf("?", tempIndex);
                    endIndex = searchContent.IndexOf(",size[", tempIndex);
                    itemImageDownloadUrl += searchContent.Substring(tempIndex, endIndex - tempIndex) + ",size[300x300],qual[80]&call=url[file:std.image]";
                }

                // Still get the SKU, so start search from start of the item row
                strToFind = "<td class=\"txt-left\">";
                tempIndex = searchContent.IndexOf(strToFind);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    endIndex = searchContent.IndexOf("</td>", tempIndex);
                    itemSku = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                    fRet = true;
                }
            }
            else
            {
                // If it contains "<td class=\"first text-left\">" THEN no image
                strToFind = "<td class=\"first text-left\">";
                tempIndex = searchContent.IndexOf(strToFind);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    endIndex = searchContent.IndexOf("</td>", tempIndex);
                    itemSku = searchContent.Substring(tempIndex, endIndex - tempIndex);
                    fRet = true;
                }
            }
            if (itemSku != null)
            {
                // 11/05/2016: Add url_key
                // outputFile.Write("sku,categories,name,price,short_description,description,base_image,small_image,thumbnail_image,additional_attributes,url_key");
                // Append SKU to Product Name, to make it unique
                // 11/15/2016 Replace "54V5417-*" with "54V5417-z". * is not allowed in sku. 
                // There ae only 3 skus - 54V5417-*14102B-26, 54V5417-*18132B-261, 54V5417-*26182B-261
                itemSku = itemSku.Trim();
                itemSku = LatinToAscii(itemSku);
                itemSku = itemSku.Replace("54V5417-*", "54V5417-z");
                if (itemImageDownloadUrl != null)
                {
                    DownloadRemoteImageFile(itemImageDownloadUrl, strBaseFolder + "product_images\\items\\" + itemSku + "_base.jpg", fUpdateItemImageCache);
                    itemImageUrl = "/items/" + itemSku + "_base.jpg";
                }
            }
            // Check if itemSku already exists, set it to null and return false
            int value = 0;
            if (!skuDictionary.TryGetValue(itemSku, out value))
            {
                skuDictionary.Add(itemSku, value);
            }
            else
            {
                itemSku = null;
                Console.WriteLine("****INFO**** ITEM ALREADY PRESENT: " + itemSku);
                return false;
            }

            // Append attribute values to short description. This will depend on headerType
            // Find Extra descrpt class or populate attributes
            string descrpt = "";
            switch (headerType)
            {
                case ProductHeaderType.SKU_Dimensions_1:
                case ProductHeaderType.SKU_DimensionsLxWxH_1:
                    // Read Dimensions 1
                    descrpt = ExtractDimensions(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Dimensions_Color_1:
                    // Read Dimensions 1
                    descrpt = ExtractDimensions(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Dimensions_Description_1:
                    // Read Dimensions 1
                    descrpt = ExtractDimensions(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_Color_1:
                case ProductHeaderType.SKU_Description_Color_2:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_Dimensions_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Dimensions 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDimensions(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_Print_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Print 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractPrint(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Length_Color_1:
                    // Read Length 1
                    descrpt = ExtractLength(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_WireThickness_Length_Color_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Length 2
                    tempIndex = endIndex;
                    descrpt = ExtractLength(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 3
                    tempIndex = endIndex; 
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Size_StrungUnStrung_1:
                case ProductHeaderType.SKU_Size_Description_1:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex;
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Size_Color_1:
                case ProductHeaderType.SKU_Size_Color_2:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Size_Description_Color_1:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 3
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Type_Color_1:
                    // Read Type 1
                    descrpt = ExtractType(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;

                case ProductHeaderType.SKU_Type_DescriptionLxW_1:
                case ProductHeaderType.SKU_Type_DescriptionLxWxFront_1:
                case ProductHeaderType.SKU_Type_Description_1:
                    // Read Type 1
                    descrpt = ExtractType(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Color_Size_1:
                    // Read Color 1
                    descrpt = ExtractColor(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Size 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractSize(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Color_Print_Size_2:
                    // Read Color 1
                    descrpt = ExtractColor(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Print 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractPrint(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Size 3
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractSize(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_Size_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Size 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractSize(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Print_Size_1:
                    // Read Print 1
                    descrpt = ExtractPrint(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Size 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractSize(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Finish_1:
                    // Read Finish 1
                    descrpt = ExtractFinish(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Finish_Size_1:
                    // Read Finish 1
                    descrpt = ExtractFinish(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Size 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractSize(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Finish_Description_1:
                    // Read Finish 1
                    descrpt = ExtractFinish(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Finish_Lights_1:
                    // Read Finish 1
                    descrpt = ExtractFinish(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Lights 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractLights(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Width_Finish_1:
                    // Read Description 1
                    descrpt = ExtractWidth(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractFinish(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Width_Finish_Lights_1:
                    // Read Width 1
                    descrpt = ExtractWidth(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractFinish(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Lights 3
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractLights(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Size_Finish_Lights_1:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractFinish(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Lights 3
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractLights(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Style_Finish_Lights_1:
                    // Read Style 1
                    descrpt = ExtractStyle(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractFinish(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Lights 3
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractLights(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_Size_Color_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Size 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractSize(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 3
                    tempIndex = endIndex; // read third descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_PacksPerCase_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>Packs per Case: " + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_Finish_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Finish 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractFinish(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>Finish: " + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Style_1:
                    // Read Style 1
                    descrpt = ExtractStyle(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Style_Color_1:
                    // Read Style 1
                    descrpt = ExtractStyle(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Category_Color_1:
                    // Read Category 1
                    descrpt = ExtractCategory(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Category_Sizes_1:
                    // Read Category 1
                    descrpt = ExtractCategory(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Size 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractSize(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Category_Dimensions_Color_1:
                    // Read Category 1
                    descrpt = ExtractCategory(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Dimensions 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDimensions(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Color 3
                    tempIndex = endIndex; // read third descrpt
                    descrpt = ExtractColor(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Color_1:
                case ProductHeaderType.SKU_Color_2:
                case ProductHeaderType.SKU_Color_4:
                    // Read Color 1
                    descrpt = ExtractColor(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Color_Description_1:
                    // Read Color 1
                    descrpt = ExtractColor(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>Packs per Case: " + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Size_1:
                case ProductHeaderType.SKU_SizeWxDxH_1:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Size_NumberOfPockets_1:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2 - Pockets
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>Pockets: " + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Size_Print_1:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Print 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractPrint(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Print_1:
                    // Read Print 1
                    descrpt = ExtractPrint(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Quantity_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>Quantity: " + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Length_1:
                    // Read Length 1
                    descrpt = ExtractLength(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Diameter_Length_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Length 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractLength(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Description_Lights_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Lights 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractLights(searchContent, tempIndex, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Arms_Description_1:
                case ProductHeaderType.SKU_Description_Connectors_1:
                case ProductHeaderType.SKU_Description_String_1:
                case ProductHeaderType.SKU_Description_TicketSize_1:
                case ProductHeaderType.SKU_Glass_Connectors_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2
                    tempIndex = endIndex; // read second descrpt
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.SKU_Available_1:
                case ProductHeaderType.SKU_Connectors_1:
                case ProductHeaderType.SKU_DescriptionWxHxD_1:
                case ProductHeaderType.SKU_DescriptionsWxDxH_1:
                case ProductHeaderType.SKU_DescriptionLxWxH_1:
                case ProductHeaderType.SKU_DescriptionWxH_1:
                case ProductHeaderType.SKU_DescriptionsWxH_1:
                case ProductHeaderType.SKU_DescriptionLxW_1:
                case ProductHeaderType.SKU_DescriptionHxLeg_1:
                case ProductHeaderType.SKU_DescriptionHxDiameter_1:
                case ProductHeaderType.SKU_DescriptionWxDxH_1:
                case ProductHeaderType.SKU_DescriptionLxWxFront_1:
                case ProductHeaderType.SKU_DescriptionWxD_1:
                case ProductHeaderType.SKU_DescriptionHxWxD_1:
                case ProductHeaderType.SKU_DescriptionHxWxL_1:
                case ProductHeaderType.SKU_Description_1:
                case ProductHeaderType.SKU_Description_2:
                case ProductHeaderType.SKU_Diameter_1:
                case ProductHeaderType.SKU_Item_1:
                case ProductHeaderType.SKU_Material_1:
                case ProductHeaderType.SKU_Options_1:
                case ProductHeaderType.SKU_TicketSize_1:
                default:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
            }

            // Find first price only
            strToFind = "<span class=\"price\">";
            tempIndex = searchContent.IndexOf(strToFind);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                endIndex = searchContent.IndexOf("</span>", tempIndex);
                itemPrice = searchContent.Substring(tempIndex, endIndex - tempIndex).Replace("$", "").Replace(",", "");
                if (itemPrice == "N/A") itemPrice = "0.01";
            }
            return fRet;
        }

        static int [] ExtractQuantityPriceBreaks(string headerRow, string strInputFilePath)
        {
            if (headerRow.Contains("<td>1+</td><td rowspan="))
            {
                return null;
            }
            else if (headerRow.Contains("<td>1+</td><td>3+</td><td rowspan="))
            {
                return new int[] { 1, 3 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td rowspan="))
            {
                return new int[] { 1, 2 };
            }
            else if (headerRow.Contains("<td>1+</td><td>5+</td><td rowspan="))
            {
                return new int[] { 1, 5 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td rowspan="))
            {
                return new int[] { 1, 6 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>5+</td><td rowspan="))
            {
                return new int[] { 1, 2, 5 };
            }
            else if (headerRow.Contains("<td>1+</td><td>3+</td><td>5+</td><td rowspan="))
            {
                return new int[] { 1, 3, 5 };
            }
            else if (headerRow.Contains("<td>1+</td><td>10+</td><td rowspan="))
            {
                return new int[] { 1, 10 };
            }
            else if (headerRow.Contains("<td>1+</td><td>12+</td><td rowspan="))
            {
                return new int[] { 1, 12 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>3+</td><td>12+</td><td rowspan="))
            {
                return new int[] { 1, 2, 3, 12 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>3+</td><td>13+</td><td rowspan="))
            {
                return new int[] { 1, 2, 3, 13 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>5+</td><td>10+</td><td rowspan="))
            {
                return new int[] { 1, 2, 5, 10 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>10+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 1, 2, 10, 20 };
            }
            else if (headerRow.Contains("<td>1+</td><td>3+</td><td>4+</td><td>12+</td><td rowspan="))
            {
                return new int[] { 1, 3, 4, 12 };
            }
            else if (headerRow.Contains("<td>1+</td><td>3+</td><td>5+</td><td>10+</td><td rowspan="))
            {
                return new int[] { 1, 3, 5, 10 };
            }
            else if (headerRow.Contains("<td>1+</td><td>5+</td><td>10+</td><td rowspan="))
            {
                return new int[] { 1, 5, 10 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>10+</td><td rowspan="))
            {
                return new int[] { 1, 6, 10 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>4+</td><td>15+</td><td rowspan="))
            {
                return new int[] { 1, 2, 4, 15 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>6+</td><td>15+</td><td rowspan="))
            {
                return new int[] { 1, 2, 6, 15 };
            }
            else if (headerRow.Contains("<td>1+</td><td>2+</td><td>8+</td><td>12+</td><td rowspan="))
            {
                return new int[] { 1, 2, 8, 12 };
            }
            else if (headerRow.Contains("<td>1+</td><td>3+</td><td>6+</td><td>12+</td><td rowspan="))
            {
                return new int[] { 1, 3, 6, 12 };
            }
            else if (headerRow.Contains("<td>1+</td><td>3+</td><td>10+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 1, 3, 10, 20 };
            }
            else if (headerRow.Contains("<td>1+</td><td>5+</td><td>15+</td><td rowspan="))
            {
                return new int[] { 1, 5, 15 };
            }
            else if (headerRow.Contains("<td>1+</td><td>5+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 1, 5, 25 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>12+</td><td rowspan="))
            {
                return new int[] { 1, 6, 12 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>24+</td><td rowspan="))
            {
                return new int[] { 1, 6, 24 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 1, 6, 25 };
            }
            else if (headerRow.Contains("<td>1+</td><td>4+</td><td>8+</td><td rowspan="))
            {
                return new int[] { 1, 4, 8 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>25+</td><td>100+</td><td rowspan="))
            {
                return new int[] { 1, 6, 25, 100 };
            }
            else if (headerRow.Contains("<td>1+</td><td>4+</td><td>8+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 1, 4, 8, 20 };
            }
            else if (headerRow.Contains("<td>1+</td><td>4+</td><td>10+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 1, 4, 10, 25 };
            }
            else if (headerRow.Contains("<td>1+</td><td>4+</td><td>16+</td><td>32+</td><td rowspan="))
            {
                return new int[] { 1, 4, 16, 32 };
            }
            else if (headerRow.Contains("<td>1+</td><td>5+</td><td>10+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 1, 5, 10, 20 };
            }
            else if (headerRow.Contains("<td>1+</td><td>5+</td><td>10+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 1, 5, 10, 25 };
            }
            else if (headerRow.Contains("<td>5+</td><td>10+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 5, 10, 25 };
            }
            else if (headerRow.Contains("<td>1+</td><td>3+</td><td>12+</td><td>24+</td><td rowspan="))
            {
                return new int[] { 1, 3, 12, 24 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>12+</td><td>24+</td><td rowspan="))
            {
                return new int[] { 1, 6, 12, 24 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>12+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 1, 6, 12, 25 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>12+</td><td>36+</td><td rowspan="))
            {
                return new int[] { 1, 6, 12, 36 };
            }
            else if (headerRow.Contains("<td>1+</td><td>6+</td><td>24+</td><td>48+</td><td rowspan="))
            {
                return new int[] { 1, 6, 24, 48 };
            }
            else if (headerRow.Contains("<td>1+</td><td>12+</td><td>24+</td><td rowspan="))
            {
                return new int[] { 1, 12, 24 };
            }
            else if (headerRow.Contains("<td>1+</td><td>12+</td><td>24+</td><td>72+</td><td rowspan="))
            {
                return new int[] { 1, 12, 24, 72 };
            }
            else if (headerRow.Contains("<td>1+</td><td>5+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 1, 5, 20 };
            }
            else if (headerRow.Contains("<td>1+</td><td>10+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 1, 10, 20 };
            }
            else if (headerRow.Contains("<td>1+</td><td>10+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 1, 10, 25 };
            }
            else if (headerRow.Contains("<td>1+</td><td>7+</td><td>25+</td><td>40+</td><td rowspan="))
            {
                return new int[] { 1, 7, 25, 40 };
            }
            else if (headerRow.Contains("<td>1+</td><td>8+</td><td>21+</td><td>42+</td><td rowspan="))
            {
                return new int[] { 1, 8, 21, 42 };
            }
            else if (headerRow.Contains("<td>1+</td><td>10+</td><td>20+</td><td>50+</td><td rowspan="))
            {
                return new int[] { 1, 10, 20, 50 };
            }
            else if (headerRow.Contains("<td>1+</td><td>10+</td><td>25+</td><td>50+</td><td rowspan="))
            {
                return new int[] { 1, 10, 25, 50 };
            }
            else if (headerRow.Contains("<td>1+</td><td>10+</td><td>25+</td><td>100+</td><td rowspan="))
            {
                return new int[] { 1, 10, 25, 100 };
            }
            else if (headerRow.Contains("<td>1+</td><td>24+</td><td rowspan="))
            {
                return new int[] { 1, 24 };
            }
            else if (headerRow.Contains("<td>1+</td><td>25+</td><td rowspan="))
            {
                return new int[] { 1, 25 };
            }
            else if (headerRow.Contains("<td>1+</td><td>100+</td><td rowspan="))
            {
                return new int[] { 1, 100 };
            }
            else if (headerRow.Contains("<td>1+</td><td>144+</td><td rowspan="))
            {
                return new int[] { 1, 144};
            }
            else if (headerRow.Contains("<td>1+</td><td>24+</td><td>144+</td><td rowspan="))
            {
                return new int[] { 1, 24, 144 };
            }
            else if (headerRow.Contains("<td>1+</td><td>48+</td><td>144+</td><td rowspan="))
            {
                return new int[] { 1, 48, 144 };
            }
            else if (headerRow.Contains("<td>1+</td><td>20+</td><td>50+</td><td rowspan="))
            {
                return new int[] { 1, 20, 50 };
            }
            else if (headerRow.Contains("<td>1+</td><td>20+</td><td>80+</td><td rowspan="))
            {
                return new int[] { 1, 20, 80 };
            }
            else if (headerRow.Contains("<td>1+</td><td>20+</td><td>100+</td><td rowspan="))
            {
                return new int[] { 1, 20, 100 };
            }
            else if (headerRow.Contains("<td>1+</td><td>30+</td><td>50+</td><td rowspan="))
            {
                return new int[] { 1, 30, 50 };
            }
            else if (headerRow.Contains("<td>1+</td><td>48+</td><td>96+</td><td rowspan="))
            {
                return new int[] { 1, 48, 96 };
            }
            else if (headerRow.Contains("<td>1+</td><td>11+</td><td>101+</td><td rowspan="))
            {
                return new int[] { 1, 11, 101 };
            }
            else if (headerRow.Contains("<td>1+</td><td>50+</td><td>150+</td><td rowspan="))
            {
                return new int[] { 1, 50, 150 };
            }
            else if (headerRow.Contains("<td>1+</td><td>50+</td><td>200+</td><td rowspan="))
            {
                return new int[] { 1, 50, 200 };
            }
            else if (headerRow.Contains("<td>1+</td><td>100+</td><td>200+</td><td rowspan="))
            {
                return new int[] { 1, 100, 200 };
            }
            else if (headerRow.Contains("<td>1+</td><td>100+</td><td>500+</td><td rowspan="))
            {
                return new int[] { 1, 100, 500 };
            }
            else if (headerRow.Contains("<td>1+</td><td>10+</td><td>1000+</td><td rowspan="))
            {
                return new int[] { 1, 10, 1000 };
            }
            else if (headerRow.Contains("<td>1+</td><td>100+</td><td>1000+</td><td rowspan="))
            {
                return new int[] { 1, 100, 1000 };
            }
            else if (headerRow.Contains("<td>2+</td><td>3+</td><td>6+</td><td rowspan="))
            {
                return new int[] { 2, 3, 6 };
            }
            else if (headerRow.Contains("<td>2+</td><td>4+</td><td>6+</td><td rowspan="))
            {
                return new int[] { 2, 4, 6 };
            }
            else if (headerRow.Contains("<td>2+</td><td>4+</td><td>8+</td><td rowspan="))
            {
                return new int[] { 2, 4, 8 };
            }
            else if (headerRow.Contains("<td>2+</td><td>10+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 2, 10, 20 };
            }
            else if (headerRow.Contains("<td>2+</td><td>11+</td><td>20+</td><td rowspan="))
            {
                return new int[] { 2, 11, 20 };
            }
            else if (headerRow.Contains("<td>2+</td><td>8+</td><td>16+</td><td rowspan="))
            {
                return new int[] { 2, 8, 16 };
            }
            else if (headerRow.Contains("<td>2+</td><td>16+</td><td rowspan="))
            {
                return new int[] { 2, 16 };
            }
            else if (headerRow.Contains("<td>4+</td><td>6+</td><td>8+</td><td rowspan="))
            {
                return new int[] { 4, 6, 8 };
            }
            else if (headerRow.Contains("<td>5+</td><td>10+</td><td>15+</td><td rowspan="))
            {
                return new int[] { 5, 10, 15 };
            }
            else if (headerRow.Contains("<td>12+</td><td>36+</td><td>72+</td><td>144+</td><td rowspan="))
            {
                return new int[] { 12, 36, 72, 144 };
            }
            else if (headerRow.Contains("<td>6+</td><td>10+</td><td rowspan="))
            {
                return new int[] { 6, 10 };
            }
            else if (headerRow.Contains("<td>4+</td><td rowspan="))
            {
                return new int[] { 4 };
            }
            else if (headerRow.Contains("<td>4+</td><td>32+</td><td rowspan="))
            {
                return new int[] { 4, 32 };
            }
            else
            {
                Console.WriteLine("***WARNING***: No matching Price Break Header in file: " + strInputFilePath + "\nHeader: " + headerRow);
            }
            return null;
        }

        static void ExtractMultiTierPrices(string searchContent, string sku, int [] qtyBreaks, string strInputFilePath, string strOutputFilePath)
        {
            string output = "";
            int nIndex = 0;
            string itemPrice = "";
            int tempIndex, endIndex;
            string strToFind = "<span class=\"price\">";
            // Find all price entries
            tempIndex = searchContent.IndexOf(strToFind);
            while (tempIndex >= 0 && nIndex < qtyBreaks.Length)
            {
                tempIndex += strToFind.Length;
                endIndex = searchContent.IndexOf("</span>", tempIndex);
                itemPrice = searchContent.Substring(tempIndex, endIndex - tempIndex).Replace("$", "").Replace(",", "");
                tempIndex = searchContent.IndexOf(strToFind, endIndex);
                // Special Case: Some pages like www.americanretailsupply.com/64993/785423/A-La-Carte-Bags--Boxes/Green-Gardens-Collection-Bags-and-Boxes.htm
                // have two column headers where single price starts from middle. Skip those rows??
                // Logic: Second condition will ensure that there is one more row  (tempIndex >0) after the first row (nIndex=0)
                if (itemPrice != "N/A" && (nIndex > 0 || tempIndex > 0))
                {
                    using (StreamWriter outputFile = new StreamWriter(strOutputFilePath, true))
                    {
                        output = String.Format("{0},All Websites [USD],ALL GROUPS,{1},{2}", sku, qtyBreaks[nIndex], itemPrice);
                        outputFile.WriteLine(output);
                    }
                }
                nIndex++;
                if (nIndex == qtyBreaks.Length && tempIndex > 0)
                {
                    Console.Write("***WARNING***: EXTRA PRICE BREAK Entry in file (fix manually): " + strInputFilePath + "\nItemRow: " + searchContent);
                }
            }

        }

        static bool ExtractItemsFromContents(string searchContents, string strInputFilePath, string strOutputFilePath, 
            string productCategory, string productName, string productDescription, string productImageUrl, string productText, 
            ref int productItemsCount, ref int itemImageCount, int startIndex, string strBaseFolder)
        {
            bool fRet = true;
            ProductHeaderType headerType = ProductHeaderType.Unknown;
            int lastPointer = startIndex;

            string headerRow = ExtractItemsHeaderRow(searchContents, lastPointer, out lastPointer, out headerType);
            // Console.WriteLine(headerType.ToString() + ": " + headerRow);
            if (headerType == ProductHeaderType.CustomerService_Resources)
            {
                Console.WriteLine("***WARNING***: Skipping CUSTOMER SERVICE Header file: " + strInputFilePath);
                return false;
            }

            string itemRow = null;
            string strMultiTierPriceFile = strOutputFilePath.Replace(".csv", "-price-tiers.csv");
            int[] priceBreakArray = ExtractQuantityPriceBreaks(headerRow, strInputFilePath);
            while ((itemRow = ExtractNextItemRow(searchContents, lastPointer, out lastPointer, headerType)) != null)
            {
                string sku = null;
                string shortDescription = "<ul>"; // start it here
                string itemImageUrl = null;
                string itemPrice = null;
                string attributes = ""; // set to blank
                string output = "";
                string additionalImage = productImageUrl;

                GetItemData(itemRow, headerType, out sku, ref shortDescription, out itemImageUrl, out itemPrice, ref attributes, strBaseFolder);
                if (sku != null)
                {
                    productItemsCount++;
                    // Complete short description
                    if (productText != null)
                    {
                        shortDescription += "<li>" + productText + "</li>";
                    }
                    shortDescription += "</ul>";
                    // use product image if item image is null
                    if (itemImageUrl == null)
                    {
                        itemImageUrl = additionalImage;
                        additionalImage = "";
                    }
                    else
                    {
                        itemImageCount++;
                    }
                    string item_name = productName.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " ") + " - " + sku;
                    string item_url_key = item_name.Replace(" - ","-").Replace("&", "").Replace(",", "").Replace("/", "").Replace("  ", " ").Trim().Replace(" ", "-").ToLower();
                    output = String.Format("{0},\"{1}\",\"{2}\",{3},\"{4}\",\"{5}\",\"{6}\",\"{7}\",\"{8}\",\"{9}\",\"{10}\",\"{11}\"",
                        sku,
                        productCategory.Replace("\"", "\"\""),
                        item_name,
                        itemPrice,
                        shortDescription.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                        productDescription.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                        itemImageUrl, itemImageUrl, itemImageUrl,
                        "",//attributes.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                        additionalImage,
                        item_url_key
                        );

                    // append link and close file
                    using (StreamWriter outputFile = new StreamWriter(strOutputFilePath, true))
                    {
                        outputFile.Write(output);
                        outputFile.WriteLine(hardCodedItemFieldsValues);
                    }

                    // if the product has multi-tier pricing
                    if (priceBreakArray != null)
                    {
                        ExtractMultiTierPrices(itemRow, sku, priceBreakArray, strInputFilePath, strMultiTierPriceFile);
                    }
                    // Console.WriteLine(output);
                }
            }
            return fRet;
        }

        static string DownloadStringFromUrl(string hostUrl)
        {
            string productFileContents = null;

            try
            {
                using (WebClient client = new WebClient())
                {
                    // Download file to get the viewstate
                    productFileContents = client.DownloadString(hostUrl);
                }
            }
            catch (Exception ex)
            {
                Console.Out.WriteLine("****EXCEPTION****" + ex.Message);
            }
            return productFileContents;
        }

        static string DownloadSwatchContent(string hostUrl, string swatchControlId, string viewState, string filePath)
        {
            string swatchContent = null;
            string postData = null;

            if (!fUpdateSwatchContentCache && File.Exists(filePath))
            {
                swatchContent = File.ReadAllText(filePath);
                return swatchContent;
            }

            // postData = String.Format("ctl00$SM1=ctl00$Main$SwatchPanel|{0}&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKMjAyNzg2MTA2Ng9kFgJmD2QWBAIBD2QWBGYPZBYCZg9kFgICAQ8WAh4EVGV4dAUaQmFieSBhbmQgSnV2ZW5pbGUgR2lmdHdyYXBkAgEPZBYEAgEPFgIfAGVkAgMPFgIfAAWCAVJldGFpbCBzdG9yZSBmaXh0dXJlcyBzdG9yZSBzdXBwbGllcyBwYWNrYWdpbmcgcHJpY2UgbWFya2luZyBkaXNwbGF5IGZpeHR1cmVzIGFuZCBwb2ludCBvZiBzYWxlIGNvbXB1dGVyIHN5c3RlbXMgZm9yIFJldGFpbCBTdG9yZXNkAgMQZGQWBAICDw9kFgIeCW9ua2V5ZG93bgWuAWlmKGV2ZW50LndoaWNoIHx8IGV2ZW50LmtleUNvZGUpe2lmICgoZXZlbnQud2hpY2ggPT0gMTMpIHx8IChldmVudC5rZXlDb2RlID09IDEzKSkge2RvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjdGwwMF9TZWFyY2hCdXR0b24nKS5jbGljaygpO3JldHVybiBmYWxzZTt9fSBlbHNlIHtyZXR1cm4gdHJ1ZX07IGQCAw8PFgIeCEltYWdlVXJsBTMvVGVtcGxhdGVzL0FtZXJpY2FuUmV0YWlsU3VwcGx5VjIvSW1hZ2VzL3NwYWNlci5naWYWAh4Hb25jbGljawWHAWlmKGdldElEKCdjdGwwMF9TZWFyY2hQaHJhc2UnKS52YWx1ZSA9PSAnJykgeyBhbGVydCgnUGxlYXNlIGVudGVyIGEgc2VhcmNoIHRlcm0nKTsgZ2V0SUQoJ2N0bDAwX1NlYXJjaFBocmFzZScpLmZvY3VzKCk7IHJldHVybiBmYWxzZTsgfWQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFg8FEmN0bDAwJFNlYXJjaEJ1dHRvbgUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDM4BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzkFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQ0MAUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDQxBRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0NDMFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQ0NAUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDM1BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzMFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQzNwUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDM0BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzEFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQ0MgUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQzNDY0BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzaYbrTK9Vv77xdrtXIqERebur3KrQ%3D%3D&__VIEWSTATEGENERATOR=986F59E2&ctl00$SearchPhrase=keyword%20search&ctl00$Main$Quantity1756633=1&ctl00$Main$Quantity1756635=1&ctl00$Main$Quantity1756634=1&ctl00$Main$Quantity1756638=1&ctl00$Main$Quantity1756637=1&ctl00$Main$Quantity1756636=1&{0}.x=21&{0}.y=26", "ctl00$Main$Swatch1041443");
            postData = String.Format("ctl00$SM1=ctl00$Main$SwatchPanel|{0}&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKMjAyNzg2MTA2Ng9kFgJmD2QWBAIBD2QWBGYPZBYCZg9kFgICAQ8WAh4EVGV4dAUaQmFieSBhbmQgSnV2ZW5pbGUgR2lmdHdyYXBkAgEPZBYEAgEPFgIfAGVkAgMPFgIfAAWCAVJldGFpbCBzdG9yZSBmaXh0dXJlcyBzdG9yZSBzdXBwbGllcyBwYWNrYWdpbmcgcHJpY2UgbWFya2luZyBkaXNwbGF5IGZpeHR1cmVzIGFuZCBwb2ludCBvZiBzYWxlIGNvbXB1dGVyIHN5c3RlbXMgZm9yIFJldGFpbCBTdG9yZXNkAgMQZGQWBAICDw9kFgIeCW9ua2V5ZG93bgWuAWlmKGV2ZW50LndoaWNoIHx8IGV2ZW50LmtleUNvZGUpe2lmICgoZXZlbnQud2hpY2ggPT0gMTMpIHx8IChldmVudC5rZXlDb2RlID09IDEzKSkge2RvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjdGwwMF9TZWFyY2hCdXR0b24nKS5jbGljaygpO3JldHVybiBmYWxzZTt9fSBlbHNlIHtyZXR1cm4gdHJ1ZX07IGQCAw8PFgIeCEltYWdlVXJsBTMvVGVtcGxhdGVzL0FtZXJpY2FuUmV0YWlsU3VwcGx5VjIvSW1hZ2VzL3NwYWNlci5naWYWAh4Hb25jbGljawWHAWlmKGdldElEKCdjdGwwMF9TZWFyY2hQaHJhc2UnKS52YWx1ZSA9PSAnJykgeyBhbGVydCgnUGxlYXNlIGVudGVyIGEgc2VhcmNoIHRlcm0nKTsgZ2V0SUQoJ2N0bDAwX1NlYXJjaFBocmFzZScpLmZvY3VzKCk7IHJldHVybiBmYWxzZTsgfWQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFg8FEmN0bDAwJFNlYXJjaEJ1dHRvbgUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDM4BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzkFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQ0MAUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDQxBRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0NDMFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQ0NAUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDM1BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzMFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQzNwUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQxNDM0BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzEFGGN0bDAwJE1haW4kU3dhdGNoMTA0MTQ0MgUYY3RsMDAkTWFpbiRTd2F0Y2gxMDQzNDY0BRhjdGwwMCRNYWluJFN3YXRjaDEwNDE0MzaYbrTK9Vv77xdrtXIqERebur3KrQ%3D%3D&__VIEWSTATEGENERATOR=986F59E2&ctl00$SearchPhrase=keyword%20search&ctl00$Main$Quantity1756633=1&ctl00$Main$Quantity1756635=1&ctl00$Main$Quantity1756634=1&ctl00$Main$Quantity1756638=1&ctl00$Main$Quantity1756637=1&ctl00$Main$Quantity1756636=1&{0}.x=21&{0}.y=26", 
                swatchControlId);

            // FOR www.americanretailsupply.com/64719/51748/Sullivan-GiftWrap/Geometrics-Giftwrap.html
            //postData = "ctl00$SM1=ctl00$Main$SwatchPanel|ctl00$Main$Swatch331136&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKMjAyNzg2MTA2Ng9kFgJmD2QWBAIBD2QWBGYPZBYCZg9kFgICAQ8WAh4EVGV4dAVCR2lmdCBXcmFwIGFuZCBSZXRhaWwgU3RvcmUgUGFja2FnaW5nLCBTdG9yZSBTdXBwbGllcyBhbmQgR2lmdCBXcmFwZAIBD2QWBAIBDxYCHwAFLWdlb21ldHJpYyB3cmFwcGluZyBwYXBlciwgZ2VvbWV0cmljIGdpZnQgd3JhcGQCAw8WAh8ABUJHaWZ0IFdyYXAgYW5kIFJldGFpbCBTdG9yZSBQYWNrYWdpbmcsIFN0b3JlIFN1cHBsaWVzIGFuZCBHaWZ0IFdyYXBkAgMQZGQWBAICDw9kFgIeCW9ua2V5ZG93bgWuAWlmKGV2ZW50LndoaWNoIHx8IGV2ZW50LmtleUNvZGUpe2lmICgoZXZlbnQud2hpY2ggPT0gMTMpIHx8IChldmVudC5rZXlDb2RlID09IDEzKSkge2RvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjdGwwMF9TZWFyY2hCdXR0b24nKS5jbGljaygpO3JldHVybiBmYWxzZTt9fSBlbHNlIHtyZXR1cm4gdHJ1ZX07IGQCAw8PFgIeCEltYWdlVXJsBTMvVGVtcGxhdGVzL0FtZXJpY2FuUmV0YWlsU3VwcGx5VjIvSW1hZ2VzL3NwYWNlci5naWYWAh4Hb25jbGljawWHAWlmKGdldElEKCdjdGwwMF9TZWFyY2hQaHJhc2UnKS52YWx1ZSA9PSAnJykgeyBhbGVydCgnUGxlYXNlIGVudGVyIGEgc2VhcmNoIHRlcm0nKTsgZ2V0SUQoJ2N0bDAwX1NlYXJjaFBocmFzZScpLmZvY3VzKCk7IHJldHVybiBmYWxzZTsgfWQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFggFEmN0bDAwJFNlYXJjaEJ1dHRvbgUXY3RsMDAkTWFpbiRTd2F0Y2gzMzEwMzEFF2N0bDAwJE1haW4kU3dhdGNoMzMxMDMzBRdjdGwwMCRNYWluJFN3YXRjaDMzMTA0MwUXY3RsMDAkTWFpbiRTd2F0Y2g3MDE1MTgFF2N0bDAwJE1haW4kU3dhdGNoMzMxMTM2BRdjdGwwMCRNYWluJFN3YXRjaDcwMTgxNgUXY3RsMDAkTWFpbiRTd2F0Y2gzMzEwNDlzcibti2OUkSEZ3J0b9wYnrtrsZA%3D%3D&__VIEWSTATEGENERATOR=986F59E2&ctl00$SearchPhrase=keyword%20search&ctl00$Main$Quantity663562=1&ctl00$Main$Quantity663666=1&ctl00$Main$Swatch331136.x=20&ctl00$Main$Swatch331136.y=28";
            //postData = String.Format("ctl00$SM1=ctl00$Main$SwatchPanel|{0}&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKMjAyNzg2MTA2Ng9kFgJmD2QWBAIBD2QWBGYPZBYCZg9kFgICAQ8WAh4EVGV4dAVCR2lmdCBXcmFwIGFuZCBSZXRhaWwgU3RvcmUgUGFja2FnaW5nLCBTdG9yZSBTdXBwbGllcyBhbmQgR2lmdCBXcmFwZAIBD2QWBAIBDxYCHwAFLWdlb21ldHJpYyB3cmFwcGluZyBwYXBlciwgZ2VvbWV0cmljIGdpZnQgd3JhcGQCAw8WAh8ABUJHaWZ0IFdyYXAgYW5kIFJldGFpbCBTdG9yZSBQYWNrYWdpbmcsIFN0b3JlIFN1cHBsaWVzIGFuZCBHaWZ0IFdyYXBkAgMQZGQWBAICDw9kFgIeCW9ua2V5ZG93bgWuAWlmKGV2ZW50LndoaWNoIHx8IGV2ZW50LmtleUNvZGUpe2lmICgoZXZlbnQud2hpY2ggPT0gMTMpIHx8IChldmVudC5rZXlDb2RlID09IDEzKSkge2RvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjdGwwMF9TZWFyY2hCdXR0b24nKS5jbGljaygpO3JldHVybiBmYWxzZTt9fSBlbHNlIHtyZXR1cm4gdHJ1ZX07IGQCAw8PFgIeCEltYWdlVXJsBTMvVGVtcGxhdGVzL0FtZXJpY2FuUmV0YWlsU3VwcGx5VjIvSW1hZ2VzL3NwYWNlci5naWYWAh4Hb25jbGljawWHAWlmKGdldElEKCdjdGwwMF9TZWFyY2hQaHJhc2UnKS52YWx1ZSA9PSAnJykgeyBhbGVydCgnUGxlYXNlIGVudGVyIGEgc2VhcmNoIHRlcm0nKTsgZ2V0SUQoJ2N0bDAwX1NlYXJjaFBocmFzZScpLmZvY3VzKCk7IHJldHVybiBmYWxzZTsgfWQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFggFEmN0bDAwJFNlYXJjaEJ1dHRvbgUXY3RsMDAkTWFpbiRTd2F0Y2gzMzEwMzEFF2N0bDAwJE1haW4kU3dhdGNoMzMxMDMzBRdjdGwwMCRNYWluJFN3YXRjaDMzMTA0MwUXY3RsMDAkTWFpbiRTd2F0Y2g3MDE1MTgFF2N0bDAwJE1haW4kU3dhdGNoMzMxMTM2BRdjdGwwMCRNYWluJFN3YXRjaDcwMTgxNgUXY3RsMDAkTWFpbiRTd2F0Y2gzMzEwNDlzcibti2OUkSEZ3J0b9wYnrtrsZA%3D%3D&__VIEWSTATEGENERATOR=986F59E2&ctl00$SearchPhrase=keyword%20search&ctl00$Main$Quantity663562=1&ctl00$Main$Quantity663666=1&{0}.x=20&{0}.y=28",
            postData = String.Format("ctl00$SM1=ctl00$Main$SwatchPanel|{0}&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE={1}&__VIEWSTATEGENERATOR=986F59E2&ctl00$SearchPhrase=keyword%20search&ctl00$Main$Quantity663562=1&ctl00$Main$Quantity663666=1&{0}.x=20&{0}.y=28",
            //postData = String.Format("ctl00$SM1=ctl00$Main$SwatchPanel|{0}&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=&__VIEWSTATEGENERATOR=986F59E2&{0}.x=20&{0}.y=28",
                swatchControlId, viewState);

            try
            {
                Random random = new Random();
                using (WebClient client = new WebClient())
                {
                    // Download file to get the viewstate
                    string productFileContent = client.DownloadString(hostUrl);


                    client.Headers.Add("user-agent", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; .NET CLR 1.0.3705;)");
                    client.Headers.Add("Content-Type", "application/x-www-form-urlencoded");
                    client.Headers.Add("X-MicrosoftAjax", "Delta=true");
                    client.Headers.Add("Origin", "chrome-extension://apcedakaoficjlofohhcmkkljehnmebp");
                    client.Headers.Add("Accept", "*/*");
                    client.Headers.Add("Cache-Control", "no-cache");
                    //client.Headers.Add("Accept-Encoding", "gzip, deflate");
                    //client.Headers.Add("Accept-Language", "en-US,en;q=0.8");
                    client.Headers.Add("Cookie", "shopperId8=283a69b0-a08e-400f-b6ae-6a06d88160e4; ASP.NET_SessionId=2jjygg55s3tz5mbxxjj1wnyt; __utma=57035450.947927119.1434389616.1477291505.1477356088.50; __utmc=57035450; __utmz=57035450.1476242357.40.2.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); Cache:LoginTriggerPage=SecurePage");
                    client.CachePolicy = new RequestCachePolicy(RequestCacheLevel.BypassCache);

                    postData = String.Format("ctl00$SM1=ctl00$Main$SwatchPanel|{0}&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE={1}&__VIEWSTATEGENERATOR=986F59E2&ctl00$SearchPhrase=keyword%20search&ctl00$Main$Quantity663562=1&ctl00$Main$Quantity663666=1&{0}.x=20&{0}.y=28",
                        swatchControlId, WebUtility.UrlEncode(viewState));

                    swatchContent = client.UploadString(hostUrl + "?randomNumber=" + random.Next().ToString(), postData);

                    // write the content to the Cache
                    File.WriteAllText(filePath, swatchContent);

                    //Console.WriteLine(swatchControlId + " swatch: " + hostUrl + "\n" +postData + "\n" + swatchContent);
                }
            }
            catch (Exception ex)
            {
                Console.Out.WriteLine("****EXCEPTION****" + ex.Message);
            }
            return swatchContent;
        }

        static string hardCodedItemFieldsHeader = ",store_view_code,attribute_set_code,product_type,weight,product_online,tax_class_name,visibility,qty,out_of_stock_qty,website_id,product_websites";
        static string hardCodedItemFieldsValues = ",,ARS_Default,simple,1.0,1,Taxable Goods,\"Catalog, Search\",1,0,1,base";

        public static void DownloadRemoteImageFile(string uri, string fileName, bool fUpdateCache)
        {
            if (File.Exists(fileName) && !fUpdateCache)
            {
                return;
            }

            HttpWebRequest request = (HttpWebRequest)WebRequest.Create(uri);
            HttpWebResponse response = (HttpWebResponse)request.GetResponse();

            // Check that the remote file was found. The ContentType
            // check is performed since a request for a non-existent
            // image file might be redirected to a 404-page, which would
            // yield the StatusCode "OK", even though the image was not
            // found.
            if ((response.StatusCode == HttpStatusCode.OK ||
                response.StatusCode == HttpStatusCode.Moved ||
                response.StatusCode == HttpStatusCode.Redirect) &&
                response.ContentType.StartsWith("image", StringComparison.OrdinalIgnoreCase))
            {

                // if the remote file was found, download oit
                using (Stream inputStream = response.GetResponseStream())
                using (Stream outputStream = File.OpenWrite(fileName))
                {
                    byte[] buffer = new byte[4096];
                    int bytesRead;
                    do
                    {
                        bytesRead = inputStream.Read(buffer, 0, buffer.Length);
                        outputStream.Write(buffer, 0, bytesRead);
                    } while (bytesRead != 0);
                }
            }
        }

        static void Main(string[] args)
        {
            string strFolder = "D:\\ARS\\ARS_Web_2\\";
            int productCount = 0;
            int totalItemsCount = 0;
            int productImageCount = 0;
            int itemImageCount = 0;

            int subTotalItemsCount = 0;
            int productItemsCount = 0;
            int currentSubFileCounter = 0;
            int categoryCounter = 0;
            const int MAX_ITEMS_IN_ONE_FILE = 4300;

            if (args.Length > 0)
            {
                if (args.Length >= 2)
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
                // Erase categories outfile file in write mode and write headers
                using (StreamWriter outputFile = new StreamWriter(args[0] + "-categories.csv"))
                {
                    outputFile.WriteLine("category,description,meta_title,meta_keywords,meta_description,image_url,original_file_url");
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
                string output = "";
                string fileContents = "";
                string strFilePath = strFolder + fileName;

                string categoryDescription = "";
                string categoryName = "No Category"; // Last portion of the bread-crumb
                string metaTitle = "";
                string metaKeywords = "";
                string metaDescription = ""; 

                if (productCount == 0 || subTotalItemsCount >= MAX_ITEMS_IN_ONE_FILE)
                {
                    currentSubFileCounter++; // increment the index used in file division
                    // Erase outfile file in write mode and write headers
                    using (StreamWriter outputFile = new StreamWriter(args[0] + "-" + currentSubFileCounter.ToString() + ".csv"))
                    {
                        outputFile.Write("sku,categories,name,price,short_description,description,base_image,small_image,thumbnail_image,additional_attributes,additional_images,url_key");
                        outputFile.WriteLine(hardCodedItemFieldsHeader);
                    }
                    using (StreamWriter outputFile = new StreamWriter(args[0] + "-" + currentSubFileCounter.ToString() + "-price-tiers.csv"))
                    {
                        outputFile.WriteLine("sku,tier_price_website,tier_price_customer_group,tier_price_qty,tier_price");
                    }
                    subTotalItemsCount = 0;
                }
                productCount++;
                productItemsCount = 0;

                try
                {
                    strFilePath = strFilePath.Replace("/", "\\");
                    //Console.WriteLine("Processing Product File - " + strFilePath);
                    // Read the file as text
                    using (StreamReader inputFile = new StreamReader(strFilePath))
                    {
                        fileContents = inputFile.ReadToEnd();
                    }

                    int lastPointer = 0;
                    string productCategory = ExtractProductHierarchy(fileContents, 0, out lastPointer, out categoryName, strFilePath);
                    if (productCategory == null)
                    {
                        Console.WriteLine("***ERROR***: Found CORRUPTED BREAD CRUMB file: " + strFilePath);
                        continue;
                    }
                    // Remove special characters from category
                    productCategory = LatinToAscii(productCategory);
                    string productDescription = ExtractProductDescription(fileContents, lastPointer, ref lastPointer);
                    if (productDescription == null)
                    {
                        Console.WriteLine("***WARNING***: Product Description was missing in file: " + strFilePath);
                        productDescription = "No Description";
                    }

                    string productImageUrl = ExtractProductImageUrl(fileContents, lastPointer, out lastPointer);
                    if (productImageUrl != null)
                    {
                        productImageUrl = productImageUrl.Replace(".eetoolset.com", "");
                        productImageCount++;
                    }
                    else
                    {
                        productImageUrl = "";
                    }

                    // Move the pointer to Find Product Variant lines
                    if ((lastPointer = fileContents.IndexOf("<div id=\"ctl00_Main_TablePanel\">")) != -1)
                    {
                        string productText = null;
                        string productName = ExtractProductNameAndText(fileContents, lastPointer, out lastPointer, out productText);
                        if (ExtractItemsFromContents(fileContents, strFilePath, args[0] + "-" + currentSubFileCounter.ToString() + ".csv",
                            productCategory, productName, productDescription, productImageUrl, productText,
                            ref productItemsCount, ref itemImageCount, lastPointer, strFolder))
                        {
                            subTotalItemsCount += productItemsCount;
                            totalItemsCount += productItemsCount;
                            categoryDescription = productDescription;
                        }
                    }
                    // Route the swatch page to a different function..... ctl00_Main_SwatchPanel
                    else if ((lastPointer = fileContents.IndexOf("<div id=\"ctl00_Main_SwatchPanel\">")) != -1)
                    {
                        lastPointer += "<div id=\"ctl00_Main_SwatchPanel\">".Length;
                        string swatchControlId = null;
                        string swatchImageUrl = "";
                        string swatchImageDownloadUrl = null;
                        string swatchName = "";
                        int swatchItemsCount = 0;
                        // Find the host URL
                        // <form name="aspnetForm" method="post" action="
                        string strTofind = "<form name=\"aspnetForm\" method=\"post\" action=\"";
                        string hostUrl = "";
                        string viewState = "";
                        int tempIndex = fileContents.IndexOf(strTofind);
                        if (tempIndex > 0)
                        {
                            tempIndex += strTofind.Length;
                            int endIndex = fileContents.IndexOf("\"", tempIndex);
                            hostUrl = fileContents.Substring(tempIndex, endIndex - tempIndex);
                        }
                        // Download file from Web to get latest veiw state
                        //string productFileContents = DownloadStringFromUrl(hostUrl);
                        string productFileContents = fileContents;
                        strTofind = "<input type=\"hidden\" name=\"__VIEWSTATE\" id=\"__VIEWSTATE\" value=\"";
                        tempIndex = productFileContents.IndexOf(strTofind);
                        if (tempIndex > 0)
                        {
                            tempIndex += strTofind.Length;
                            int endIndex = productFileContents.IndexOf("\" />", tempIndex);
                            viewState = productFileContents.Substring(tempIndex, endIndex - tempIndex);
                        }
                        while ((swatchControlId = ExtractNextSwatch(fileContents, ref lastPointer, ref swatchImageUrl, ref swatchName, ref swatchImageDownloadUrl)) != null)
                        {
                            //Console.WriteLine(String.Format("Swatch Id: {0}. Image: {1}. Name={2}", swatchControlId, swatchImageUrl, swatchName));
                            string swatchContent = null;
                            string swatchContentFilePath = strFolder + "product_images\\swatch_content\\" + swatchControlId.Replace("ctl00_Main_", "") + ".txt";
                            swatchContent = DownloadSwatchContent(hostUrl, swatchControlId.Replace("_", "$"), viewState, swatchContentFilePath);
                            if (swatchContent.Contains("Error.aspx"))
                            {
                                Console.WriteLine(String.Format("****SWATCHES ERROR DOWNLOADING**** Swatch Id: {0}. Name: {1}. Content: {2} in file: {3}",
                                    swatchControlId, swatchName, swatchContent, strFilePath));
                                continue;
                            }
                            string swatchUnits = null;
                            // TODO- Get better image and Extract <div class="units"> content to create productText
                            string swatchName2 = ExtractSwatchName2AndText(swatchContent, 0, out swatchUnits, swatchName);
                            // TRIM both names before matching, found a leading space in www.americanretailsupply.com\64687\730878\Sign-Cards\Seasonal-Posters.html
                            if (String.Compare(swatchName.Trim(), swatchName2.Trim(), true) != 0)
                            {
                                // Ignore following three cases
                                // ****SWATCHES ERROR **** Swatch Id: ctl00_Main_Swatch1023459. Name2: Delicate DAccor  XB599 Holographic. Name=Delicate Décor  XB599 Holographic do not match in file: D:\ARS\ARS_Web_3\www.americanretailsupply.com\65000\780724\Christmas-Giftwrap\JR-Holiday-and-Christmas.html
                                // ****SWATCHES ERROR **** Swatch Id: ctl00_Main_Swatch1035365. Name2: GW-8224  Decked Out DAccor. Name=GW-8224  Decked Out Décor do not match in file: D:\ARS\ARS_Web_3\www.americanretailsupply.com\64723\50843\Holiday-Giftwrap\Christmas-and-Holiday-Giftwrap-1.html
                                // ****SWATCHES ERROR **** Swatch Id: ctl00_Main_Swatch1029014. Name2: GW-8143 CafAc Cupcakes & Frosting. Name=GW-8143 Café Cupcakes & Frosting do not match in file: D:\ARS\ARS_Web_3\www.americanretailsupply.com\64719\51722\Sullivan-GiftWrap\Birthday-Celebration--Party-Giftwrap.html
                                if (!swatchControlId.Equals("ctl00_Main_Swatch1023459") &&
                                    !swatchControlId.Equals("ctl00_Main_Swatch1035365") &&
                                    !swatchControlId.Equals("ctl00_Main_Swatch1029014"))
                                {
                                    Console.WriteLine(String.Format("****SWATCHES ERROR **** Swatch Id: {0}. Name2: {1}. Name={2} do not match in file: {3}",
                                        swatchControlId, swatchName2, swatchName, strFilePath));
                                    continue;
                                }
                            }
                            int swatchLocationPointer = 0;
                            swatchItemsCount = 0;
                            // Download image
                            if (swatchImageDownloadUrl != null)
                            {
                                swatchImageUrl = "/swatches/" + swatchControlId.Replace("ctl00_Main_", "") + "_base.jpg";
                                DownloadRemoteImageFile(swatchImageDownloadUrl, strFolder + "product_images" + swatchImageUrl.Replace("/", "\\"), fUpdateSwatchImageCache);
                            }
                            itemImageCount++; // For the swatch images
                            if (ExtractItemsFromContents(swatchContent, strFilePath, args[0] + "-" + currentSubFileCounter.ToString() + ".csv",
                                productCategory, swatchName, productDescription, swatchImageUrl.Replace(".eetoolset.com", ""), swatchUnits,
                                ref swatchItemsCount, ref itemImageCount, swatchLocationPointer, strFolder))
                            {
                                productItemsCount += swatchItemsCount;
                            }
                        }
                        subTotalItemsCount += productItemsCount;
                        totalItemsCount += productItemsCount;
                        categoryDescription = productDescription;
                    }
                    // Route for the Category page if it contains "</select> per page</span><div class="
                    else if ((lastPointer = fileContents.IndexOf("</select> per page</span><div class=")) != -1)
                    {
                        // extract the table with image links and append that to product description.
                        // 5/10/2017: Do not read sub category table.
                        //string subCategoryTable = ExtractSubCategoryTable(fileContents, lastPointer, strFilePath);
                        string subCategoryTable = null; // just set it null to bypass next block
                        if (subCategoryTable != null)
                        {
                            // replace ALL occurences of ".eetoolset.com/img????.jpg?set" with ".eetoolset.com/img?set"
                            string strTofind = ".eetoolset.com/img";
                            int tempIndex = subCategoryTable.IndexOf(strTofind);
                            int endIndex = -1;
                            while (tempIndex > 0)
                            {
                                tempIndex += strTofind.Length;
                                endIndex = subCategoryTable.IndexOf("?set", tempIndex);
                                if (endIndex > tempIndex)
                                {
                                    subCategoryTable = subCategoryTable.Remove(tempIndex, endIndex - tempIndex);
                                }
                                tempIndex = subCategoryTable.IndexOf(strTofind, endIndex);
                            }
                            // use online eporia images until we can crate an image server
                            subCategoryTable = subCategoryTable.Replace("<img src=\"../Templates/AmericanRetailSupplyV2", "<img src=\"http://www.americanretailsupply.com/Templates/AmericanRetailSupplyV2");
                            subCategoryTable = subCategoryTable.Replace("<img src=\"../../", "<img src=\"http://");
                            subCategoryTable = subCategoryTable.Replace("&amp;", "&");
                            if (subCategoryTable.Contains(" href=\"../"))
                            {
                                subCategoryTable = subCategoryTable.Replace(" href=\"../", " href=\"");
                                subCategoryTable = subCategoryTable.Replace(" href=\"", " href=\"/");
                            }
                            else
                            {
                                String[] strFileNameParts = fileName.Split('/');
                                subCategoryTable = subCategoryTable.Replace(" href=\"", " href=\"/"+ strFileNameParts[1] + "/");
                            }
                            subCategoryTable = subCategoryTable.Replace("\n", " ").Replace("\r", " ").Trim();
                            categoryDescription = productDescription + subCategoryTable;
                        }
                        else
                        {
                            categoryDescription = productDescription;
                        }
                    }
                    // Now write that to the category pages.
                    if (categoryDescription != "")
                    {
                        ExtractMetaTags(fileContents, out metaTitle, out metaKeywords, out metaDescription, strFilePath);
                        using (StreamWriter outputFile = new StreamWriter(args[0] + "-categories.csv", true))
                        {
                            outputFile.WriteLine(String.Format("\"{0}\",\"{1}\",\"{2}\",\"{3}\",\"{4}\",\"{5}\",\"{6}\"",
                                productCategory.Replace("\"", "\"\""),
                                categoryDescription.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                                metaTitle.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                                metaKeywords.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                                metaDescription.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                                productImageUrl, 
                                fileName));
                        }
                        categoryCounter++;
                    }
                }
                catch (Exception ex)
                {
                    output = output + "FAIL";
                    Console.WriteLine("Failed to process fileName - " + fileName + ". Exception: " + ex.Message);
                }
                Console.WriteLine(String.Format("{7} Categories. {0} SubFile. {1} File. {2} sub item count. {3} product images. {4} item images. {5} total items. Filename: {6}",
                    currentSubFileCounter, productCount, subTotalItemsCount, productImageCount, itemImageCount, totalItemsCount, fileName, categoryCounter));
            }

            Console.WriteLine(String.Format("{5} Categories. {0} files. {1} Items read. {2} product images, {3} Item images, {4} sub files.", 
                productCount, totalItemsCount, productImageCount, itemImageCount, currentSubFileCounter, categoryCounter));
            Console.WriteLine("Processing complete. Press any key to exit.");
            Console.ReadLine();
        }
    }
}
