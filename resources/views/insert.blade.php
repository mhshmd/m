@extends('layouts.layout')

@section('title') Entry Soal - toSTIS.net @stop

@section('customCSS')
    <link href={{url("css/fileinput.min.css")}} rel="stylesheet" type="text/css">
@stop

@section('body')
    <body>
@stop

@section('mainBody')
    <div class="container" style="margin:0; padding:0">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-lock"></span> Entry Soal</h3>
                    </div>
                    <div class="panel-body">
                        <div class="col-md-8 col-xs-8">
                            <form role="form" action={{route('insertQuest')}} method="POST" enctype="multipart/form-data">
                              {{ csrf_field() }}  
                                <div class="row">
                                  <div class="col-md-7">
                                    <div class="radio-inline">
                                      <label><input type="radio" name="forWhat" value="2" checked='checked'>Try Out Umum</label>
                                    </div> 
                                  </div>
                                  <div class="col-md-5">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Try Out ID" name="tryOutId" value="{{$tryOutId or ""}}">
                                    </div>
                                    <div class="form-group">
                                        <input style="display: none;" type="text" class="form-control" name="name" placeholder="Try Out Name" value="{{$tryOutName or ""}}">
                                    </div>
                                    <div class="form-group">
                                        <input style="display: none;" type="text" class="form-control" name="type" placeholder="Try Out Type" value="{{$type or ""}}">
                                    </div>
                                  </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Upload gambar (optional)</label>
                                  <input id="input-7" name="qPictPath" multiple type="file" class="file file-loading" data-allowed-file-extensions='["png", "jpg", "jpeg", "gif"]'>
                                </div>
                                <div class="form-group">
                                  <label class="control-label">Quest Intro (optional)</label>
                                    <div class="form-group">
                                          <input style="display: none;" type="text" style="width:150px" class="form-control" placeholder="Quest Intro ID" name="questIntroId" value="{{$questIntroId or ""}}">
                                    </div>
                                    <textarea style="display: none;" class="form-control" id="questIntro" name='questIntro' rows="3" placeholder="Text">{{$questIntroText or ""}}</textarea>
                                </div>                               
                                <div>
                                    <div class="form-group col-md-12" style="padding:0">
                                        <select class='form-control subMaterialInit' id="subject" name="subjectId">
                                          <option value="1" {{($lastSubjectSelected==1)?"selected":""}}>MATEMATIKA</option>
                                          <option value="2" {{($lastSubjectSelected==2)?"selected":""}}>BAHASA INGGRIS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" id="questtext" name='text' onkeyup="Preview.Update()" rows="6" placeholder="Text" autofocus></textarea>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="a"  name='a' onkeyup="Preview1.Update()" placeholder="Option a">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="b" name='b' onkeyup="Preview2.Update()" placeholder="Option b">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="c" name='c' onkeyup="Preview3.Update()" placeholder="Option c">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="d" name='d' onkeyup="Preview4.Update()" placeholder="Option d">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="e" name='e' onkeyup="Preview5.Update()" placeholder="Option e">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="answer" name='answer' placeholder="Answer">
                                </div> 
                                <div class="form-group" style="padding: 0 3px">
                                    <input style="display: none;" value="5" type="text" class="form-control" id="level" name='questLevel' placeholder="Level">
                                </div>             
                                <div class="form-group">
                                    <input list="submaterial" class='form-control' name="subMaterialId" value="{{$subMaterialName or ""}}">
                                    <datalist id="submaterial" class="submaterial" name="subMaterialId">

                                    </datalist>
                                </div>         
                                <button type="submit" class="btn btn-default">Simpan</button>
                            </form>
                        </div>
                        <div class="col-md-4 col-xs-4" style="margin-top:175pt">
                            <h4>Preview</h4>
                            <ol>
                                <li>
                                    <span id="preview1"></span><span id="previewBuffer"></span>
                                    <ol type="A">
                                        <li><label><input type="radio" name="no3" value="1"> <span id="previewA"></span><span id="previewBufferA"></span></label></li>
                                        <li><label><input type="radio" name="no3" value="2"> <span id="previewB"></span><span id="previewBufferB"></span></label></li>
                                        <li><label><input type="radio" name="no3" value="3"> <span id="previewC"></span><span id="previewBufferC"></span></label></li>
                                        <li><label><input type="radio" name="no3" value="4"> <span id="previewD"></span><span id="previewBufferD"></span></label></li>
                                        <li><label><input type="radio" name="no3" value="5"> <span id="previewE"></span><span id="previewBufferE"></span></label></li>
                                    </ol> 
                                </li>
                            </ol>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  @stop

  @section('customJS')
    <script>
    $(document).ready(function(){
            var subject = document.getElementById('subject').value;      
            var link = "{{route('insertQuestAjax')}}?subject=";
            $.ajax({url: link+subject, success: function(result){
                    $("datalist").append(result); 
                }});
            $(".subMaterialInit").change(function(){
                $("option").remove(".del");
                var subject = document.getElementById('subject').value;      
                var link = "{{route('insertQuestAjax')}}?subject=";
                $.ajax({url: link+subject, success: function(result){
                        $("datalist").append(result); 
                    }});
            });
    });
    </script>

    <script type="text/x-mathjax-config">
          MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});
    </script>

    <script type="text/javascript" src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_CHTML">
    </script>

    <script src={{url("js/fileinput.min.js")}}></script>

    <script>
        var Preview = {
          delay: 150,   

          preview: null,   
          buffer: null,  

          timeout: null,    
          mjRunning: false,  
          mjPending: false,  
          oldText: null,  

          Init: function () {
            this.preview = document.getElementById("preview1");
            this.buffer = document.getElementById("previewBuffer");
          },

          SwapBuffers: function () {
            var buffer = this.preview, preview = this.buffer;
            this.buffer = buffer; this.preview = preview;
            buffer.style.visibility = "hidden"; buffer.style.position = "absolute";
            preview.style.position = ""; preview.style.visibility = "";
          },

          Update: function () {
            if (this.timeout) {clearTimeout(this.timeout)}
            this.timeout = setTimeout(this.callback,this.delay);
          },

          CreatePreview: function () {
            Preview.timeout = null;
            if (this.mjPending) return;
            var text = document.getElementById("questtext").value;
            if (text === this.oldtext) return;
            if (this.mjRunning) {
              this.mjPending = true;
              MathJax.Hub.Queue(["CreatePreview",this]);
            } else {
              this.buffer.innerHTML = this.oldtext = text;
              this.mjRunning = true;
              MathJax.Hub.Queue(
            ["Typeset",MathJax.Hub,this.buffer],
            ["PreviewDone",this]
              );
            }
          },

          PreviewDone: function () {
            this.mjRunning = this.mjPending = false;
            this.SwapBuffers();
          }

        };

        Preview.callback = MathJax.Callback(["CreatePreview",Preview]);
        Preview.callback.autoReset = true;  // make sure it can run more than once

    </script>

    <script>
        var Preview1 = {
          delay: 150,   

          preview: null,   
          buffer: null,  

          timeout: null,    
          mjRunning: false,  
          mjPending: false,  
          oldText: null,  

          Init: function () {
            this.preview = document.getElementById("previewA");
            this.buffer = document.getElementById("previewBufferA");
          },

          SwapBuffers: function () {
            var buffer = this.preview, preview = this.buffer;
            this.buffer = buffer; this.preview = preview;
            buffer.style.visibility = "hidden"; buffer.style.position = "absolute";
            preview.style.position = ""; preview.style.visibility = "";
          },

          Update: function () {
            if (this.timeout) {clearTimeout(this.timeout)}
            this.timeout = setTimeout(this.callback,this.delay);
          },

          CreatePreview: function () {
            Preview1.timeout = null;
            if (this.mjPending) return;
            var text = document.getElementById("a").value;
            if (text === this.oldtext) return;
            if (this.mjRunning) {
              this.mjPending = true;
              MathJax.Hub.Queue(["CreatePreview",this]);
            } else {
              this.buffer.innerHTML = this.oldtext = text;
              this.mjRunning = true;
              MathJax.Hub.Queue(
            ["Typeset",MathJax.Hub,this.buffer],
            ["PreviewDone",this]
              );
            }
          },

          PreviewDone: function () {
            this.mjRunning = this.mjPending = false;
            this.SwapBuffers();
          }

        };

        Preview1.callback = MathJax.Callback(["CreatePreview",Preview1]);
        Preview1.callback.autoReset = true;  // make sure it can run more than once

    </script>

    <script>
        var Preview2 = {
          delay: 150,   

          preview: null,   
          buffer: null,  

          timeout: null,    
          mjRunning: false,  
          mjPending: false,  
          oldText: null,  

          Init: function () {
            this.preview = document.getElementById("previewB");
            this.buffer = document.getElementById("previewBufferB");
          },

          SwapBuffers: function () {
            var buffer = this.preview, preview = this.buffer;
            this.buffer = buffer; this.preview = preview;
            buffer.style.visibility = "hidden"; buffer.style.position = "absolute";
            preview.style.position = ""; preview.style.visibility = "";
          },

          Update: function () {
            if (this.timeout) {clearTimeout(this.timeout)}
            this.timeout = setTimeout(this.callback,this.delay);
          },

          CreatePreview: function () {
            Preview2.timeout = null;
            if (this.mjPending) return;
            var text = document.getElementById("b").value;
            if (text === this.oldtext) return;
            if (this.mjRunning) {
              this.mjPending = true;
              MathJax.Hub.Queue(["CreatePreview",this]);
            } else {
              this.buffer.innerHTML = this.oldtext = text;
              this.mjRunning = true;
              MathJax.Hub.Queue(
            ["Typeset",MathJax.Hub,this.buffer],
            ["PreviewDone",this]
              );
            }
          },

          PreviewDone: function () {
            this.mjRunning = this.mjPending = false;
            this.SwapBuffers();
          }

        };

        Preview2.callback = MathJax.Callback(["CreatePreview",Preview2]);
        Preview2.callback.autoReset = true;  // make sure it can run more than once

    </script>

    <script>
        var Preview3 = {
          delay: 150,   

          preview: null,   
          buffer: null,  

          timeout: null,    
          mjRunning: false,  
          mjPending: false,  
          oldText: null,  

          Init: function () {
            this.preview = document.getElementById("previewC");
            this.buffer = document.getElementById("previewBufferC");
          },

          SwapBuffers: function () {
            var buffer = this.preview, preview = this.buffer;
            this.buffer = buffer; this.preview = preview;
            buffer.style.visibility = "hidden"; buffer.style.position = "absolute";
            preview.style.position = ""; preview.style.visibility = "";
          },

          Update: function () {
            if (this.timeout) {clearTimeout(this.timeout)}
            this.timeout = setTimeout(this.callback,this.delay);
          },

          CreatePreview: function () {
            Preview3.timeout = null;
            if (this.mjPending) return;
            var text = document.getElementById("c").value;
            if (text === this.oldtext) return;
            if (this.mjRunning) {
              this.mjPending = true;
              MathJax.Hub.Queue(["CreatePreview",this]);
            } else {
              this.buffer.innerHTML = this.oldtext = text;
              this.mjRunning = true;
              MathJax.Hub.Queue(
            ["Typeset",MathJax.Hub,this.buffer],
            ["PreviewDone",this]
              );
            }
          },

          PreviewDone: function () {
            this.mjRunning = this.mjPending = false;
            this.SwapBuffers();
          }

        };

        Preview3.callback = MathJax.Callback(["CreatePreview",Preview3]);
        Preview3.callback.autoReset = true;  // make sure it can run more than once

    </script>

    <script>
        var Preview4 = {
          delay: 150,   

          preview: null,   
          buffer: null,  

          timeout: null,    
          mjRunning: false,  
          mjPending: false,  
          oldText: null,  

          Init: function () {
            this.preview = document.getElementById("previewD");
            this.buffer = document.getElementById("previewBufferD");
          },

          SwapBuffers: function () {
            var buffer = this.preview, preview = this.buffer;
            this.buffer = buffer; this.preview = preview;
            buffer.style.visibility = "hidden"; buffer.style.position = "absolute";
            preview.style.position = ""; preview.style.visibility = "";
          },

          Update: function () {
            if (this.timeout) {clearTimeout(this.timeout)}
            this.timeout = setTimeout(this.callback,this.delay);
          },

          CreatePreview: function () {
            Preview4.timeout = null;
            if (this.mjPending) return;
            var text = document.getElementById("d").value;
            if (text === this.oldtext) return;
            if (this.mjRunning) {
              this.mjPending = true;
              MathJax.Hub.Queue(["CreatePreview",this]);
            } else {
              this.buffer.innerHTML = this.oldtext = text;
              this.mjRunning = true;
              MathJax.Hub.Queue(
            ["Typeset",MathJax.Hub,this.buffer],
            ["PreviewDone",this]
              );
            }
          },

          PreviewDone: function () {
            this.mjRunning = this.mjPending = false;
            this.SwapBuffers();
          }

        };

        Preview4.callback = MathJax.Callback(["CreatePreview",Preview4]);
        Preview4.callback.autoReset = true;  // make sure it can run more than once

    </script>

    <script>
        var Preview5 = {
          delay: 150,   

          preview: null,   
          buffer: null,  

          timeout: null,    
          mjRunning: false,  
          mjPending: false,  
          oldText: null,  

          Init: function () {
            this.preview = document.getElementById("previewE");
            this.buffer = document.getElementById("previewBufferE");
          },

          SwapBuffers: function () {
            var buffer = this.preview, preview = this.buffer;
            this.buffer = buffer; this.preview = preview;
            buffer.style.visibility = "hidden"; buffer.style.position = "absolute";
            preview.style.position = ""; preview.style.visibility = "";
          },

          Update: function () {
            if (this.timeout) {clearTimeout(this.timeout)}
            this.timeout = setTimeout(this.callback,this.delay);
          },

          CreatePreview: function () {
            Preview5.timeout = null;
            if (this.mjPending) return;
            var text = document.getElementById("e").value;
            if (text === this.oldtext) return;
            if (this.mjRunning) {
              this.mjPending = true;
              MathJax.Hub.Queue(["CreatePreview",this]);
            } else {
              this.buffer.innerHTML = this.oldtext = text;
              this.mjRunning = true;
              MathJax.Hub.Queue(
            ["Typeset",MathJax.Hub,this.buffer],
            ["PreviewDone",this]
              );
            }
          },

          PreviewDone: function () {
            this.mjRunning = this.mjPending = false;
            this.SwapBuffers();
          }

        };

        Preview5.callback = MathJax.Callback(["CreatePreview",Preview5]);
        Preview5.callback.autoReset = true;  // make sure it can run more than once
    </script>

    <script>
        Preview.Init();
    </script>
    <script>
        Preview1.Init();
    </script>
    <script>
        Preview2.Init();
    </script>
    <script>
        Preview3.Init();
    </script>
    <script>
        Preview4.Init();
    </script>
    <script>
        Preview5.Init();
    </script>
@stop