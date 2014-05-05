# Based off md_to_rst.sh script written by https://gist.github.com/hugorodgerbrown
# This script was created to convert a directory full
# of html files into md equivalents. It uses
# pandoc to do the conversion.
#
# 1. Install pandoc from http://johnmacfarlane.net/pandoc/
# 2. Copy this script into the directory containing the .html files
# 3. Ensure that the script has execute permissions
# 4. Run the script
#
# By default this will keep the original .html file


cd query_outputs/htmls
FILES=*.html
for f in $FILES
do
  # extension="${f##*.}"
  filename="${f%.*}"
  echo "Converting $f to $filename.md"
  `pandoc $f -t markdown_strict -o ../mds/$filename.md`
  # uncomment this line to delete the source file.
  # rm $f
done
cd ../yamls
YAMLS=*.yaml
for y in $YAMLS
do
  # extension="${f##*.}"
  filename="${y%.*}"
   echo "Concating $y and its markdown..."
   cat $y ../mds/$filename.md >> ../concats/$y
done
