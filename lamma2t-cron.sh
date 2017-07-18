#!/bin/bash  

# 20140827 mfaure

ImgRootDir="/var/www-vhosts/mm3g.ovh/lamma2t/images"
ImgOptimisedDir="${ImgRootDir}_optimised"

# For wind
UrlStub_wind="http://www.lamma.rete.toscana.it/models/ventoemare"
ImageStub_wind="wind10m_N_web_"

# For sea swell
UrlStub_swell="http://www.lamma.rete.toscana.it/models/ww3_gfs_lr/last"
ImageStub_swell="swh_N_web_"

ImageExt=".png"
ImageExtOptimised=".optimised.png"

function downloadImage() {
    # 1st param: URL base
    UrlStub="$1"
    # 2nd param: basename of the file
    ImageStub="$2"
    # 3rd param: index
    i="$3"

    wget -q --passive "${UrlStub}/${ImageStub}${i}${ImageExt}" -O "${ImgRootDir}/${ImageStub}${i}${ImageExt}" && \
        pngcrush -q  "${ImgRootDir}/${ImageStub}${i}${ImageExt}" "${ImgOptimisedDir}/${ImageStub}${i}${ImageExtOptimised}"
}

function downloadImages() {
# First seven images are not downloaded because when the model is published,
# those seven forecasts are in the past (e.g. forecasts from 0h to 7h whereas
# model is published at 7:30am). Thus :
# ImgIndexStart: must be 1 + the min value of $myLoopInit in the .php script (here 1 + 7 = 8)
ImgIndexStart=8
ImgIndexEnd=57

for i in $(seq ${ImgIndexStart} ${ImgIndexEnd}) ; do
        downloadImage "${UrlStub_wind}" "${ImageStub_wind}" "${i}"
        downloadImage "${UrlStub_swell}" "${ImageStub_swell}" "${i}"
done
}

# 1) Remove previous downloaded images
rm -rf "${ImgRootDir}/*.${ImageExt}"
rm -rf "${ImgOptimisedDir}/*.${ImageExt}"

# 2) Download images
downloadImages
