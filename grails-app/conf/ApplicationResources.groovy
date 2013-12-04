def __bundleName = 'motoranger'

modules = {

	overrides {

		// bootstrap {
		//     defaultBundle __bundleName
		// }
		modernizr {
			defaultBundle __bundleName
		}

		"jquery-ui" {
			defaultBundle __bundleName
		}


	}

  //   'jquery-ui' {
  //       //defaultBundle 'jquery'
		// defaultBundle __bundleName

  //       resource url: 'jquery-ui/css/smoothness/jquery-ui-1.9.1.custom.min.css'
		// resource url: 'jquery-ui/js/jquery-ui-1.9.1.custom.min.js'
  //   }

	// Using jQuery File Upload Plug-in
  // 'jquery-fileupload' {
  //       dependsOn 'jquery'
  //       //defaultBundle 'jquery'
		// defaultBundle __bundleName
		
  //       resource url: 'jquery-fileupload/css/jquery.fileupload-ui.css'
  //       resource url: 'jquery-fileupload/css/jquery.fileupload-ui-noscript.css',
  //           wrapper: { s -> "<noscript>$s</noscript>" }
	
  //       resource url: 'jquery-fileupload/js/jquery.iframe-transport.js'
  //       resource url: 'jquery-fileupload/js/jquery.fileupload.js'
  //       resource url: 'jquery-fileupload/js/jquery.fileupload-fp.js'
  //       resource url: 'jquery-fileupload/js/jquery.fileupload-ui.js'
  // }
	
  //   'jquery-cslider' {
  //       dependsOn 'jquery'
  //       //defaultBundle 'jquery'
		// defaultBundle __bundleName
		
  //       resource url: 'jquery-cslider/parallax-slider.css'
  //       resource url: 'jquery-cslider/jquery.cslider.js'
  //       resource url: 'jquery-cslider/slider.js'
  //   }


	
	'jquery-plugins' {
		dependsOn 'jquery'
		//defaultBundle 'jquery'
		defaultBundle __bundleName
		
		resource url: 'jquery-plugins/jquery.migrate.min.js'
		resource url: 'jquery-plugins/jquery.textarea.js'
		resource url: 'jquery-plugins/jquery.jfontsize.js'
		resource url: 'jquery-plugins/jquery.cookie.js'
		resource url: 'jquery-plugins/jquery.masonry.js'
		resource url: 'jquery-plugins/jquery.lazyload.min.js'
	}


	common {
		dependsOn 'jquery, jquery-ui, jquery-plugins, pagedown'
		//defaultBundle 'common'
	defaultBundle __bundleName

		resource url: 'js/common.js'
	}
	//測驗專用功能
  //   exercise {
  //       dependsOn 'jquery, common'

		// defaultBundle __bundleName
		
  //       resource url: 'js/exercise.js'
  //   }
	
	application {
		dependsOn 'common'
		//defaultBundle 'common'
		defaultBundle __bundleName

		resource url: 'js/application.js'
	}

  //   webfont {
		// //defaultBundle __bundleName
  //       //use built-in fonts first
  //   	//resource url: 'js/webfont.js', disposition: 'head'
  //   }

	ie6 {
		resource url: 'universal-ie6-css/ie6.1.0.css',
			wrapper: { s -> "<!--[if IE 6]>$s<![endif]-->" }
	}

	pagedown {
		//defaultBundle 'coding-tools'
		defaultBundle __bundleName
	   
		//css
		resource url: 'pagedown/pagedown.css'
		
		
		//js
		resource url: 'pagedown/Markdown.Converter.js'
		resource url: 'pagedown/Markdown.Sanitizer.js'
		resource url: 'pagedown/Markdown.Editor.js'
	}

  //   codemirror {
  //       //defaultBundle 'coding-tools'
		// defaultBundle __bundleName
		
  //       //css
  //       resource url: 'codemirror/lib/codemirror.css'
  //       resource url: 'stylesheets/codemirror.css'
  //       //js
  //       resource url: 'codemirror/lib/codemirror.js'
  //       resource url: 'codemirror/mode/clike/clike.js'
  //       resource url: 'codemirror/mode/scheme/scheme.js'
  //       resource url: 'codemirror/mode/xml/xml.js'
  //       resource url: 'codemirror/mode/css/css.js'
  //       resource url: 'codemirror/mode/javascript/javascript.js'
  //       resource url: 'codemirror/mode/htmlmixed/htmlmixed.js'
  //       resource url: 'codemirror/addon/runmode/runmode.js'
  //       //resource url: 'codemirror/addon/fold/collapserange.js'
  //       resource url: 'codemirror/addon/fold/foldcode.js'
  //   }



  //   highlightjs {
  //       //defaultBundle 'coding-tools'
		// defaultBundle __bundleName

  //       resource url: 'highlightjs/styles/vs.css'
  //       resource url: 'highlightjs/highlight.pack.js', disposition: 'head'
  //   }
   
  //   bootswatch {
  //       dependsOn 'bootstrap-js'
  //       //defaultBundle 'bootstrap'
		// defaultBundle __bundleName
		
  //       //resource url: [dir: 'swatchmaker', file: 'swatchmaker.less'],
  //       //    attrs: [rel: 'stylesheet/less', type: 'css'],
  //       //    bundle: _bundleName
		
  //       //resource url: [dir: 'swatchmaker', file: 'swatchmaker-responsive.less'],
  //       //    attrs: [rel: 'stylesheet/less', type: 'css'],
  //       //    bundle: _bundleName

  //       resource url: 'stylesheets/bootstrap.min.css'
  //       resource url: 'stylesheets/bootstrap-responsive.min.css'
  //       resource url: 'stylesheets/docs.css'
  //   }
	
    'bootstrap-ext' {
        dependsOn 'bizstrap'
        //defaultBundle 'bootstrap'
		defaultBundle __bundleName

        resource url: 'bootstrap-ext/bootbox/bootbox.min.js'
        // resource url: 'select2/select2.js'
        // resource url: 'select2/select2_locale_eu.js'
        // resource url: 'select2/select2.css'

        resource url: 'bootstrap-ext/datepicker/css/datepicker.css'
        resource url: 'bootstrap-ext/datepicker/js/bootstrap-datepicker.js'

        // resource url: 'bootstrap-ext/timepicker/compiled/timepicker.css'
        // resource url: 'bootstrap-ext/timepicker/js/bootstrap-timepicker.js'

        // resource url: 'bootstrap-ext/bootstrap-lightbox.css'
        // resource url: 'bootstrap-ext/bootstrap-lightbox.js'
    }
	
	'font-awesome' {
		dependsOn 'bizstrap'
		//defaultBundle 'bootstrap'
		defaultBundle __bundleName
		
		//resource url: [dir: 'font-awesome/less', file: 'font-awesome.less'],
		//    attrs: [rel: 'stylesheet/less', type:'css'], bundle: _bundleName

		//resource url: [dir: 'font-awesome/less', file: 'font-awesome-ie7.less'],
		//    attrs: [rel: 'stylesheet/less', type:'css'],
		//    wrapper: { s -> "<!--[if lte IE 7]>$s<![endif]-->" }

		resource url: 'stylesheets/font-awesome.css'
		resource url: 'stylesheets/font-awesome-ie7.css',
			wrapper: { s -> "<!--[if lte IE 7]>$s<![endif]-->" }
	}
	

	tagit {
		dependsOn 'jquery, jquery-ui'
		defaultBundle __bundleName
		resource url: 'tagit/css/jquery.tagit.css'
		resource url: 'tagit/css/tagit.ui-zendesk.css'

		resource url: 'tagit/js/tag-it.js'
	}

	compass {
		dependsOn 'bizstrap, tagit, fileuploader'
		//defaultBundle 'bootstrap'
		defaultBundle __bundleName



		// resource url: 'stylesheets/bootstrap.css'

		// resource url: 'stylesheets/docs.css'

		
		resource url: 'stylesheets/screen.css'
		// resource url: 'css/main.css'

		// resource url: 'stylesheets/print.css',
		//     attrs: [media: 'print'],
		//     bundle: "print"
		// resource url: 'stylesheets/ie.css',
		//     wrapper: { s -> "<!--[if IE]>$s<![endif]-->" }
	}

	bizstrap {   
		dependsOn 'jquery'     
		defaultBundle __bundleName
		resource url: 'bizstrap/css/bootstrap.css'        
		resource url: 'bizstrap/rs-plugin/css/settings.css', attrs: [media: 'screen']
		resource url: 'bizstrap/css/rs-responsive.css', attrs: [media: 'screen']

		resource url: 'bizstrap/css/custom.css'
		resource url: 'bizstrap/css/isotope.css'
		resource url: 'bizstrap/css/color_scheme.css'
		resource url: 'bizstrap/css/flexslider.css'
		// resource url: 'bizstrap/css/jquery.fancybox.css?v=2.1.0', attrs: [media: 'screen']


		resource url: 'bizstrap/js/bootstrap.js'  
		resource url: 'bizstrap/js/jquery.flexslider-min.js'
		resource url: 'bizstrap/js/jquery.isotope.js'
		// resource url: 'bizstrap/js/jquery.fancybox.pack.js?v=2.1.0'        
		// resource url: 'bizstrap/rs-plugin/js/jquery.themepunch.plugins.min.js'
		// resource url: 'bizstrap/rs-plugin/js/jquery.themepunch.revolution.min.js'
		// resource url: 'bizstrap/js/revolution.custom.js'

		resource url: 'bizstrap/js/custom.js'

	}



}
