// https://github.com/ariya/phantomjs/wiki/API-Reference
// http://we-love-php.blogspot.fr/2012/12/create-pdf-invoices-with-html5-and-phantomjs.html
// http://dev.w3.org/csswg/css-page/#populating-margin-boxes
var page = new WebPage();
var system = require("system");
var fs = require('fs');

var id = system.args[1];
var host = system.args[2];

var size = 0;
var options;
var page_size = 'A4';
var orientation = 'portrait';
var margin_left = 25;
var margin_right = 25;
var margin_top = 10;
var margin_bottom = 10;
var header_align = 'left';
var header_text = '';
var header_pagination = 0;
var footer_align = 'left';
var footer_text = '';
var footer_pagination = 0;

var json_file = 'roadbook/' + id + '.json';
if (fs.exists(json_file) && fs.size(json_file) > 0) {
  var plainOptions = fs.read(json_file);
  options = JSON.parse(plainOptions);
  if (options && typeof options == 'object') {
    page_size = options.page_size;
    orientation = options.orientation;
    margin_left = options.margin_left;
    margin_right = options.margin_right;
    margin_top = options.margin_top;
    margin_bottom = options.margin_bottom;
    header_align = options.header_align;
    header_text = options.header_text;
    header_pagination = options.header_pagination;
    footer_align = options.footer_align;
    footer_text = options.footer_text;
    footer_pagination = options.footer_pagination;
  }
}

page.paperSize = {
  format: page_size,
  orientation: orientation,
  margin: {
    left: parseInt(margin_left) + 'mm',
    right: parseInt(margin_right) + 'mm',
    top: parseInt(margin_top) + 'mm',
    bottom: parseInt(margin_bottom) + 'mm'
  },
  header: {
    height: "0.9cm",
    contents: phantom.callback(function(pageNum, numPages) {
      var content = "<div style='font-size:10pt;text-align:" + header_align + ";'>";
      if (header_pagination) {
        content += pageNum + "/" + numPages;
      } else {
        content += header_text;
      }
      content += "<div style=''></div>";
      return content;
    })
  },
  footer: {
    height: "0.9cm",
    contents: phantom.callback(function(pageNum, numPages) {
      var content = "<div style='font-size:10pt;text-align:" + footer_align + ";'>";
      if (footer_pagination) {
        content += pageNum + "/" + numPages;
      } else {
        content += footer_text;
      }
      content += "</div>";
      return content;
    })
  }
};

page.open('http://' + host + '/roadbook/' + id + '.html?raw', function(status) {
  page.render('roadbook/pdf/' + id + '.pdf');
  phantom.exit();
});
