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
            // Return null if bcrumbs is empty: see www.americanretailsupply.com\10264\1533\Avery-Dennison-One-Line-Price-Gun\PB-1-Labels.html
            if (searchContent.IndexOf("<div id=\"bcrumbs\"></div>") != -1)
            {
                return category;
            }

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
            int descriptionIndex = searchContent.IndexOf("<div class=\"Content \" >", startIndex);
            if (descriptionIndex >= 0)
            {
                descriptionIndex += "<div class=\"Content \" >".Length;
                endIndex = searchContent.IndexOf("</div></td>", descriptionIndex);
                description = searchContent.Substring(descriptionIndex, endIndex - descriptionIndex);
            }
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
                tempIndex = searchContent.IndexOf(strToFind, tempIndex);
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
            string strToFind = "<tr class=\"mid2\">";
            int tempIndex = searchContent.IndexOf(strToFind, startIndex);
            if (tempIndex >= 0)
            {
                tempIndex += strToFind.Length;
                strToFind = "</tr>";
                endIndex = searchContent.IndexOf("</tr>", tempIndex);
                itemRow = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
            }
            if (itemRow == null)
            {
                strToFind = "<tr class=\"mid\">";
                tempIndex = searchContent.IndexOf(strToFind, startIndex);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    strToFind = "</tr>";
                    endIndex = searchContent.IndexOf("</tr>", tempIndex);
                    itemRow = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                }
            }
            if (itemRow == null)
            {
                strToFind = "<tr class=\"bot\">";
                tempIndex = searchContent.IndexOf(strToFind, startIndex);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    strToFind = "</tr>";
                    endIndex = searchContent.IndexOf("</tr>", tempIndex);
                    itemRow = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                }
            }
            if (itemRow == null)
            {
                strToFind = "<tr class=\"bot2\">";
                tempIndex = searchContent.IndexOf(strToFind, startIndex);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    strToFind = "</tr>";
                    endIndex = searchContent.IndexOf("</tr>", tempIndex);
                    itemRow = searchContent.Substring(tempIndex, endIndex - tempIndex).Trim();
                }
            }
            return itemRow;
        }

        enum ProductHeaderType
        {
            Unknown,
            Image_SKU_Description_HxWxL_1,
            Image_SKU_Description_1,
            Image_SKU_Description_Color_1,
            Image_SKU_Color_1,
            Image_SKU_Color_2,
            Image_SKU_Color_4,
            Image_SKU_Size_1,
            Image_SKU_Size_StrungUnStrung_1,
            Image_SKU_Size_Color_1,
            SKU_Color_1,
            Image_SKU_Dimensions_Color_1,
            Image_SKU_DescriptionWxLxH_1,
            Image_SKU_DescriptionWxH_1,
            Image_SKU_DimensionsLxWxH_1,
            SKU_Dimensions_1,
            Image_SKU_Length_Color_1,
            Image_SKU_1,
            SKU_Description_1,
            SKU_Description_TicketSize_1,
            SKU_Description_WxH_1,
            SKU_DescriptionsWxH_1,
            SKU_Item_1,
            SKU_1
        }

        static ProductHeaderType GetHeaderRowType(string headerRow)
        {

            ProductHeaderType headerType = ProductHeaderType.Unknown;
            if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxH)</td><td>1+</td><td rowspan=\"2\">Quantity"))
            {
                headerType = ProductHeaderType.SKU_Description_WxH_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td rowspan=\"2\">Descriptions (WxH)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_DescriptionsWxH_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (H x W x L)</td><td>1+</td>"))
            {
                headerType = ProductHeaderType.Image_SKU_Description_HxWxL_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td>1+</td><td>"))
            {
                headerType = ProductHeaderType.Image_SKU_Description_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Ticket Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Description_TicketSize_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Description (WxLxH)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_DescriptionWxLxH_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Dimensions (L x W x H)</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_DimensionsLxWxH_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td rowspan=\"2\">Item</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Item_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Description</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Description_Color_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Dimensions</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Dimensions_Color_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td rowspan=\"2\">Dimensions</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Dimensions_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Length</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Length_Color_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Color_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_Color_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>2+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Color_2;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Color</td><td>4+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Color_4;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Size_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Strung/Unstrung</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Size_StrungUnStrung_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td rowspan=\"2\">Size</td><td rowspan=\"2\">Color</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_Size_Color_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\"></td><td rowspan=\"2\">SKU</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.Image_SKU_1;
            }
            else if (headerRow.Contains("<td class=\"topfirst\" rowspan=\"2\">SKU</td><td>1+</td><td"))
            {
                headerType = ProductHeaderType.SKU_1;
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
            if (descrpt == null)
            {
                Console.WriteLine("****WARNING*****: No description in " + searchContent + " at index " + startIndex.ToString());
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

        static bool GetItemData(string searchContent, ProductHeaderType headerType, out string itemSku, 
            ref string shortDescription, out string itemImageUrl, out string itemPrice, ref string attributes)
        {
            bool fRet = false;
            itemSku = null;
            itemImageUrl = null;
            itemPrice = null; // Item not found

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
                }
                // Still get the SKU, so start search from start of the item row
                strToFind = "<td class=\"txt-left\">";
                tempIndex = searchContent.IndexOf(strToFind);
                if (tempIndex >= 0)
                {
                    tempIndex += strToFind.Length;
                    endIndex = searchContent.IndexOf("</td>", tempIndex);
                    itemSku = searchContent.Substring(tempIndex, endIndex - tempIndex);
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

            // Append attribute values to short description. This will depend on headerType
            // Find Extra descrpt class or populate attributes
            string descrpt = "";
            switch (headerType)
            {
                case ProductHeaderType.SKU_Dimensions_1:
                case ProductHeaderType.Image_SKU_DimensionsLxWxH_1:
                    // Read Dimensions 1
                    descrpt = ExtractDimensions(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.Image_SKU_Dimensions_Color_1:
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
                case ProductHeaderType.Image_SKU_Description_Color_1:
                case ProductHeaderType.Image_SKU_Length_Color_1:
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
                case ProductHeaderType.Image_SKU_Size_StrungUnStrung_1:
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
                case ProductHeaderType.SKU_Description_TicketSize_1:
                    // Read Description 1
                    descrpt = ExtractDescrpt(searchContent, 0, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    // Read Description 2: e.g. Ticket Size
                    tempIndex = endIndex;
                    descrpt = ExtractDescrpt(searchContent, tempIndex, ref endIndex);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.Image_SKU_Size_Color_1:
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
                case ProductHeaderType.SKU_Color_1:
                case ProductHeaderType.Image_SKU_Color_1:
                case ProductHeaderType.Image_SKU_Color_2:
                case ProductHeaderType.Image_SKU_Color_4:
                    // Read Color 1
                    descrpt = ExtractColor(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
                case ProductHeaderType.Image_SKU_Size_1:
                    // Read Size 1
                    descrpt = ExtractSize(searchContent, 0, ref endIndex, ref attributes);
                    if (descrpt != null)
                    {
                        shortDescription += "<li>" + descrpt + "</li>";
                    }
                    break;
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
            }
            return fRet;
        }

        static void Main(string[] args)
        {
            string strFolder = "D:\\ARS\\ARS_Web_3\\";
            int productCount = 0;
            int totalItemsCount = 0;
            int productImageCount = 0;
            int itemImageCount = 0;

            int subTotalItemsCount = 0;
            int productItemsCount = 0;
            int currentSubFileCounter = 0;
            const int MAX_ITEMS_IN_ONE_FILE = 100;

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
            string hardCodedItemFieldsHeader = ",store_view_code,attribute_set_code,product_type,weight,product_online,tax_class_name,visibility,qty,out_of_stock_qty,website_id,product_websites";
            string hardCodedItemFieldsValues = ",,Default,simple,1.0,1,Taxable Goods,\"Catalog, Search\",1,0,1,base";
            try
            {
                // Read all lines into a string Array
                productFileNames = File.ReadAllLines(args[0]);
            }
            catch (Exception ex)
            {
                Console.WriteLine("Exception: " + ex.Message + " occurred during initialization.");
                return;
            }

            // Read each fileName string from the string array
            foreach (string fileName in productFileNames)
            {
                if (productCount == 0 || subTotalItemsCount >= MAX_ITEMS_IN_ONE_FILE)
                {
                    currentSubFileCounter++; // increment the index used in file division
                    // Erase outfile file in write mode and write headers
                    using (StreamWriter outputFile = new StreamWriter(args[0] + "-" + currentSubFileCounter.ToString() + ".csv"))
                    {
                        outputFile.Write("sku,categories,name,price,short_description,description,base_image,small_image,thumbnail_image,additional_attributes");
                        outputFile.WriteLine(hardCodedItemFieldsHeader);
                    }
                    subTotalItemsCount = 0;
                }
                productCount++;
                productImageCount = 0;
                productItemsCount = 0;

                string output = "";
                string fileContents = "";
                string strFilePath = strFolder + fileName;
                strFilePath = strFilePath.Replace("/", "\\");
                //Console.WriteLine("Processing Product File - " + strFilePath);
                // Read the file as text
                using (StreamReader inputFile = new StreamReader(strFilePath))
                {
                    fileContents = inputFile.ReadToEnd();
                }

                int lastPointer = 0;

                try
                {
                    string productCategory = ExtractCategory(fileContents, lastPointer, out lastPointer);
                    if (productCategory == null) // we will skip this file.
                    {
                        Console.WriteLine("***WARNING***: Skipping file: " + strFilePath + " because it does not have Bread Crumb - Category.");
                        continue;
                    }
                    string productDescription = ExtractProductDescription(fileContents, lastPointer, out lastPointer);
                    if (productDescription == null)
                    {
                        productDescription = "";
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
                    lastPointer = fileContents.IndexOf("<div id=\"ctl00_Main_TablePanel\">", lastPointer);
                    string productText = null;
                    string productName = ExtractProductNameAndText(fileContents, lastPointer, out lastPointer, out productText);

                    ProductHeaderType headerType = ProductHeaderType.Unknown;
                    string headerRow = ExtractItemsHeaderRow(fileContents, lastPointer, out lastPointer, out headerType);
                    // Console.WriteLine(headerType.ToString() + ": " + headerRow);

                    string itemRow = null;
                    while ((itemRow = ExtractNextItemRow(fileContents, lastPointer, out lastPointer, headerType)) != null)
                    {
                        string sku = null;
                        string shortDescription = "<ul>"; // start it here
                        string itemImageUrl = null;
                        string itemPrice = null;
                        string attributes = ""; // set to blank
                        GetItemData(itemRow, headerType, out sku, ref shortDescription, out itemImageUrl, out itemPrice, ref attributes);
                        if (sku != null)
                        {
                            productItemsCount++;
                            // Complete short description
                            if (productText != null) {
                                shortDescription += "<li>" + productText + "</li>";
                            }
                            shortDescription += "</ul>";
                            // use product image if item image is null
                            if (itemImageUrl == null)
                            {
                                itemImageUrl = productImageUrl;
                            }
                            else
                            {
                                itemImageCount++;
                            }
                            // outputFile.Write("sku,categories,name,price,short_description,description,base_image,small_image,thumbnail_image,additional_attributes");
                            // Append SKU to Product Name, to make it unique
                            output = String.Format("{0},\"{1}\",\"{2}\",{3},\"{4}\",\"{5}\",\"{6}\",\"{7}\",\"{8}\",\"{9}\"",
                                sku, 
                                productCategory.Replace("\"", "\"\""),
                                productName.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " ") + " - " + sku,
                                itemPrice,
                                shortDescription.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                                productDescription.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " "),
                                itemImageUrl, itemImageUrl, itemImageUrl,
                                attributes.Replace("\"", "\"\"").Replace("\n", " ").Replace("\r", " ")
                                );

                            // append link and close file
                            using (StreamWriter outputFile = new StreamWriter(args[0] + "-" + currentSubFileCounter.ToString() + ".csv", true))
                            {
                                outputFile.Write(output);
                                outputFile.WriteLine(hardCodedItemFieldsValues);
                            }
                            // Console.WriteLine(output);
                        }
                    }
                    subTotalItemsCount += productItemsCount;
                    totalItemsCount += productItemsCount;
                }
                catch (Exception ex)
                {
                    output = output + "FAIL";
                    Console.WriteLine("Failed to process fileName - " + fileName + ". Exception: " + ex.Message);
                }
                Console.WriteLine(String.Format("{0} SubFile. {1} File. {2} sub item count. {3} product images. {4} item images. {5} total items. Filename: {6}",
                    currentSubFileCounter, productCount, subTotalItemsCount, productImageCount, itemImageCount, totalItemsCount, fileName));
            }

            Console.WriteLine(String.Format("{0} files. {1} Items read. {2} product images, {3} Item images, {4} sub files.", productCount, totalItemsCount, productImageCount, itemImageCount, currentSubFileCounter));
            Console.WriteLine("Processing complete. Press any key to exit.");
            Console.ReadLine();
        }
    }
}
