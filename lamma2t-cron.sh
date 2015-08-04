#!/bin/bash  

# 20140827 mfaure

ImgRootDir="/var/www-vhosts/mm3g.ovh/lamma2t/images"
ImgCropDir="${ImgRootDir}_1crop"
ImgOptimisedDir="${ImgRootDir}_2optimised"

UrlStub="http://www.lamma.rete.toscana.it/models/ventoemare"
ImageStub="wind10m_N_web_"
ImageExt=".png"
ImageExtCrop=".crop.png"
ImageExtOptimised=".optimised.png"

# ImgIndexStart: must be 1 + the min value of $myLoopInit in the .php script (here 1 + 7 = 8)
ImgIndexStart=8
ImgIndexEnd=38

for i in $(seq ${ImgIndexStart} $ImgIndexEnd) ; do
	wget  -q --passive "${UrlStub}/${ImageStub}${i}${ImageExt}" -O "${ImgRootDir}/${ImageStub}${i}${ImageExt}" && \
	pngcrush -q  "${ImgRootDir}/${ImageStub}${i}${ImageExt}" "${ImgOptimisedDir}/${ImageStub}${i}${ImageExtOptimised}"
done

