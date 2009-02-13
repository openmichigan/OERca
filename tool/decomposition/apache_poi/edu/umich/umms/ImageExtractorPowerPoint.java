/*
 * COPYRIGHT (c) 2009
 * The Regents of the University of Michigan
 * ALL RIGHTS RESERVED
 * 
 * Permission is granted to use, copy, create derivative works
 * and redistribute this software and such derivative works
 * for any purpose, so long as the name of The University of
 * Michigan is not used in any advertising or publicity
 * pertaining to the use of distribution of this software
 * without specific, written prior authorization.  If the
 * above copyright notice or any other identification of the
 * University of Michigan is included in any copy of any
 * portion of this software, then the disclaimer below must
 * also be included.
 * 
 * THIS SOFTWARE IS PROVIDED AS IS, WITHOUT REPRESENTATION
 * FROM THE UNIVERSITY OF MICHIGAN AS TO ITS FITNESS FOR ANY
 * PURPOSE, AND WITHOUT WARRANTY BY THE UNIVERSITY OF
 * MICHIGAN OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING
 * WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE
 * REGENTS OF THE UNIVERSITY OF MICHIGAN SHALL NOT BE LIABLE
 * FOR ANY DAMAGES, INCLUDING SPECIAL, INDIRECT, INCIDENTAL, OR
 * CONSEQUENTIAL DAMAGES, WITH RESPECT TO ANY CLAIM ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OF THE SOFTWARE, EVEN
 * IF IT HAS BEEN OR IS HEREAFTER ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGES.
 */

package edu.umich.umms;

import java.io.FileOutputStream;
import java.io.IOException;

import org.apache.poi.hslf.usermodel.SlideShow;
import org.apache.poi.hslf.usermodel.PictureData;
import org.apache.poi.hslf.HSLFSlideShow;
import org.apache.poi.hslf.model.Picture;

public class ImageExtractorPowerPoint extends ImageExtractor {

	private String inFile = "";
	private String outDir = "";
	private SlideShow ppt;
	
	/*
	 * Constructor
	 */
	public ImageExtractorPowerPoint(String inFile, String outDir) {
		this.inFile = inFile;
		this.outDir = outDir;
		
		try {
			this.ppt = new SlideShow(new HSLFSlideShow(this.inFile));
		} catch (IOException e) {
	    	System.err.println(this.inFile + " doesn't appear to be a valid PowerPoint file!");
		}
	}
	
	/*
	 * Extract images
	 */
	public int extractImages() {
        //extract all pictures contained in the presentation

		int code = 0;
		
		PictureData[] pdata = this.ppt.getPictureData();
		if (pdata != null) {
	        for (int i = 0; i < pdata.length; i++) {
	            PictureData pict = pdata[i];
	
	            // picture data
	            byte[] data = pict.getData();
	
	            int type = pict.getType();
	            String ext;
	            switch (type) {
	                case Picture.JPEG:
	                    ext = ".jpg";
	                    break;
	                case Picture.PNG:
	                    ext = ".png";
	                    break;
	                case Picture.WMF:
	                    ext = ".wmf";
	                    break;
	                case Picture.EMF:
	                    ext = ".emf";
	                    break;
	                case Picture.PICT:
	                    ext = ".pict";
	                    break;
	                case Picture.DIB:
	                    ext = ".dib";
	                    break;
	                default:
	                    continue;
	            }
	            //System.out.println("Creating image file " + this.outDir + "/image_"
				//		+ (i+1) + ext);
	            try {
		            FileOutputStream out = new FileOutputStream(this.outDir + "/image_" + (i+1) + ext);
		            out.write(data);
		            out.close();
	            } catch (IOException e) {
	                System.err.println("Caught IOException: " +  e.getMessage());
	            	code = 2;
	            }
	        }
		}
        return code;
	}
}
