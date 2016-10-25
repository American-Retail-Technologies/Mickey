﻿using System;
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

        static string ExtractProductHierarchy(string searchContent, int startIndex, out int endIndex)
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
                category += "/" + searchContent.Substring(startIndex, endIndex - startIndex).Replace("/", " ");
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

        static string ExtractProductDescription(string searchContent, int startIndex, ref int endIndex)
        {
            string description = null;
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

        static bool ExtractItemsFromContents(string searchContents, string strInputFilePath, string strOutputFilePath, 
            string productCategory, string productName, string productDescription, string productImageUrl, string productText, 
            ref int productItemsCount, ref int itemImageCount, int startIndex)
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
            while ((itemRow = ExtractNextItemRow(searchContents, lastPointer, out lastPointer, headerType)) != null)
            {
                string sku = null;
                string shortDescription = "<ul>"; // start it here
                string itemImageUrl = null;
                string itemPrice = null;
                string attributes = ""; // set to blank
                string output = "";

                GetItemData(itemRow, headerType, out sku, ref shortDescription, out itemImageUrl, out itemPrice, ref attributes);
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
                    using (StreamWriter outputFile = new StreamWriter(strOutputFilePath, true))
                    {
                        outputFile.Write(output);
                        outputFile.WriteLine(hardCodedItemFieldsValues);
                    }
                    // Console.WriteLine(output);
                }
            }
            return fRet;
        }

        static string hardCodedItemFieldsHeader = ",store_view_code,attribute_set_code,product_type,weight,product_online,tax_class_name,visibility,qty,out_of_stock_qty,website_id,product_websites";
        static string hardCodedItemFieldsValues = ",,Default,simple,1.0,1,Taxable Goods,\"Catalog, Search\",1,0,1,base";

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
            const int MAX_ITEMS_IN_ONE_FILE = 300;

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
                    string productCategory = ExtractProductHierarchy(fileContents, lastPointer, out lastPointer);
                    if (productCategory == null) // we will skip this file.
                    {
                        Console.WriteLine("***WARNING***: Skipping BLANK BREAD CRUMB file: " + strFilePath);
                        continue;
                    }
                    string productDescription = ExtractProductDescription(fileContents, lastPointer, ref lastPointer);
                    if (productDescription == null)
                    {
                        Console.WriteLine("***WARNING***: Product Description was missing in file: " + strFilePath);
                        productDescription = productCategory;
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
                            ref productItemsCount, ref itemImageCount, lastPointer))
                        {
                            subTotalItemsCount += productItemsCount;
                            totalItemsCount += productItemsCount;
                        }
                    }
                    // Route the swatch page to a different function..... ctl00_Main_SwatchPanel
                    else if ((lastPointer = fileContents.IndexOf("<div id=\"ctl00_Main_SwatchPanel\">")) != -1)
                    {
                        Console.WriteLine("***WARNING***: Skipping SWATCHES file: " + strFilePath);
                        continue;
                    }
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