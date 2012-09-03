var filesNum, currentLoadedBytes = 0;
var filesTotalSize = 0;
var uploadCount = 1;
var progress;

/***********************************  图片上传处理 开始 ************************************/
function img_fileQueueError(file, errorCode, message) {
	try {
		alert(errorCode);
		var imageName = "error.gif";
		var errorName = "";
		if (errorCode === SWFUpload.errorCode_QUEUE_LIMIT_EXCEEDED) {
			errorName = "You have attempted to queue too many files.";
		}

		if (errorName !== "") {
			alert(errorName);
			return;
		}

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			imageName = "zerobyte.gif";
			break;
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			imageName = "toobig.gif";
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
		default:
			alert(message);
			break;
		}
	} catch (ex) {
		this.debug(ex);
	}

}

function img_fileQueued(file) {
	filesTotalSize += file.size;
}

function img_fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			filesNum = numFilesQueued;
			swfu.setButtonDisabled(true);
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function img_uploadProgress(file, bytesLoaded) {	
	try {
		if(jQuery("#imageinput > p").attr("class").indexOf("mbm") == -1) {
			jQuery("#imageinput > p").addClass("mbm");
		}
		jQuery("#imageinput > p").css("display", "none");
	
		progress = new FileProgress(file, this.customSettings.upload_target);
		currentLoadedBytes += bytesLoaded;	
		var percent = Math.ceil((currentLoadedBytes / filesTotalSize) * 100);
		progress.setProgress(percent);
	} catch (ex) {
		this.debug(ex);
	}
}

function img_uploadSuccess(file, serverData) {
	try {
		jQuery("#thumbnails").css("display", "block");
		var fileData = jQuery.parseJSON(serverData);
		var fileSpitterIndex = fileData.file_name.indexOf(".");
		jQuery("#thumbnails").append("<div class='thumbWrapper fpr'><p class='thumbTitle'>"
									   + "<a href='javascript:;' onclick='cancelImgUpload(this);' class='y' title='删除'></a>"
									   + "<a id=" + fileData.file_name.substring(0, fileSpitterIndex)
									   + " href='javascript:;' onclick='setImgTitle(this);' class='z' title='设置标题'></a></p>"
									   + "<div class='thumbTitleBg'></div><img src='"
									   + fileData.file_path + fileData.file_name.substring(0, fileSpitterIndex)
									   + ".thumb" + fileData.file_name.substring(fileSpitterIndex, fileData.file_name.length)
									   + "' width='92' height='92' /></div>");
		jQuery("#thumbnails > div:last").data("picpath", fileData.file_path);
		jQuery("#thumbnails > div:last").data("pictitle", "");
		jQuery("#thumbnails > div:last").data("picname", fileData.file_name);
		jQuery("#thumbnails > div:last").data("pictype", fileData.file_type);
		jQuery("#thumbnails > div:last").data("picsize", fileData.file_size);
		jQuery("#divFileProgress #uploadCount").text(uploadCount++);
	} catch (ex) {
		this.debug(ex);
	}
}

function img_uploadComplete(file) {
	try {
		/*  I want the next upload to continue automatically so I'll call startUpload here */
		if (this.getStats().files_queued > 0) {
			this.startUpload();
		} else {
			progress.setComplete();
			jQuery("#imageinput > p").css("display", "block");
			jQuery("#thumbnails .thumbWrapper").hover(
				function() {
					jQuery(".thumbTitle", this).slideDown("fast");
					jQuery(".thumbTitleBg", this).slideDown("fast");
				},
				function() {
						jQuery(".thumbTitle", this).slideUp("fast");
						jQuery(".thumbTitleBg", this).slideUp("fast");
				}
			);
			
			updateImgInputData();
			inputMustListener();
			swfu.setButtonDisabled(false);
			filesNum = currentLoadedBytes = 0;
			filesTotalSize = 0;
			uploadCount = 1;
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function img_uploadError(file, errorCode, message) {
	var imageName =  "error.gif";
	var progress;
	try {
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Cancelled");
				//progress.toggleCancel(false);
			}
			catch (ex1) {
				this.debug(ex1);
			}
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Stopped");
				//progress.toggleCancel(true);
			}
			catch (ex2) {
				this.debug(ex2);
			}
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			imageName = "uploadlimit.gif";
			break;
		default:
			alert(message);
			break;
		}


	} catch (ex3) {
		this.debug(ex3);
	}

}

function cancelImgUpload(source) {
	jQuery(source).parent().parent().fadeOut("fast", function() {
		jQuery(source).parent().parent().remove();
		updateImgInputData();
		inputMustListener();
		swfu.setFileUploadLimit(++swfu.settings.file_upload_limit);
		
		if(!mifm_picValidate()) {
			jQuery("#imageinput > p").removeClass("mbm");
			jQuery("#thumbnails").css("display", "none");	
		}
	});
}

function setImgTitle(source) {
	jQuery("#append_parent .sInput").remove();
	var hasSet = jQuery("#" + source.id).parent().parent().data("pictitle") == "" ? false : true;
	var msg = hasSet ? jQuery("#" + source.id).parent().parent().data("pictitle") : "图片标题";
	var classstr = hasSet ? "'" : "' class='xg0'";
	var inputstr = "<div class='quickImgTitleSet'><div class='qts_arrow'></div><div class='qts_content'>"
				   + "<div class='add_f'><a href='javascript:;'>确定</a></div><input type='text' name='imgTitleInput' value='"
	               + msg + classstr + "/></div></div>";
	showInput(source.id, inputstr, function() {
		var selectStr = "#" + source.id + "_pmenu";
		jQuery(selectStr + " input").focus(
			function() {
				if(jQuery(this).val() == "图片标题") {
					jQuery(this).val("");
					jQuery(this).removeClass("xg0");
				}
		}).blur(
			function() {
				if(jQuery(this).val() == "") {
					jQuery(this).addClass("xg0");
					jQuery(this).val("图片标题");
				}
		});
		jQuery(selectStr + " a").click(function() {
			var inputvalue = jQuery(selectStr + " input").val() == "图片标题" ? "" : jQuery(selectStr + " input").val();
			jQuery("#" + source.id).parent().parent().data("pictitle", jQuery.trim(inputvalue));
			updateImgInputData();
			var hasIndicated = jQuery("#" + source.id + "_indi").length != 0 ? true : false;
			if(inputvalue != "") {
				if(!hasIndicated) {
					jQuery("#" + source.id).parent().parent().append("<div id='" + source.id + "_indi' class='thumbTitleIndicator'>⋯</div>");
				}
			} else {
				if(hasIndicated) {
					jQuery("#" + source.id + "_indi").remove();
				}
			}
			jQuery(selectStr).remove();
		});
	});
}

function updateImgInputData() {
	var picpathArray = [];
	var pictitleArray = [];
	var picnameArray = [];
	var pictypeArray = [];
	var picsizeArray = [];
	
	jQuery("#thumbnails > div").each(function() {
		picpathArray.push(encodeURIComponent(jQuery(this).data("picpath")));
		pictitleArray.push(encodeURIComponent(jQuery(this).data("pictitle")));
		picnameArray.push(encodeURIComponent(jQuery(this).data("picname")));
		pictypeArray.push(encodeURIComponent(jQuery(this).data("pictype")));
		picsizeArray.push(encodeURIComponent(jQuery(this).data("picsize")));
	});
	
	jQuery("#addPostForm input[name=picpath]").val(picpathArray);
	jQuery("#addPostForm input[name=pictitle]").val(pictitleArray);
	jQuery("#addPostForm input[name=picname]").val(picnameArray);
	jQuery("#addPostForm input[name=pictype]").val(pictypeArray);
	jQuery("#addPostForm input[name=picsize]").val(picsizeArray);
}

/***********************************  图片上传处理 结束 ************************************/

/***********************************  视频上传处理 开始 ************************************/
function video_fileQueueError(file, errorCode, message) {
	try {
		alert(errorCode);
		var imageName = "error.gif";
		var errorName = "";
		if (errorCode === SWFUpload.errorCode_QUEUE_LIMIT_EXCEEDED) {
			errorName = "You have attempted to queue too many files.";
		}

		if (errorName !== "") {
			alert(errorName);
			return;
		}

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			imageName = "zerobyte.gif";
			break;
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			imageName = "toobig.gif";
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
		default:
			alert(message);
			break;
		}
	} catch (ex) {
		this.debug(ex);
	}

}

function video_fileQueued(file) {
	filesTotalSize += file.size;
}

function video_fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			filesNum = numFilesQueued;
			swfu.setButtonDisabled(true);
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function video_uploadProgress(file, bytesLoaded) {	
	try {
		if(jQuery("#videoinputminor > p").attr("class").indexOf("mbm") == -1) {
			jQuery("#videoinputminor > p").addClass("mbm");
		}
		jQuery("#videoinputminor > p").css("display", "none");
	
		progress = new FileProgress(file, this.customSettings.upload_target);
		currentLoadedBytes += bytesLoaded;	
		var percent = Math.ceil((currentLoadedBytes / filesTotalSize) * 100);
		progress.setProgress(percent);
	} catch (ex) {
		this.debug(ex);
	}
}

function video_uploadSuccess(file, serverData) {
	try {
		jQuery("#thumbnails").css("display", "block");
		jQuery("#thumbnails").append(AC_FL_RunContent('width', '500', 'height', '375', 'allowNetworking', 'internal', 'allowScriptAccess', 'never', 'src', 'static/image/common/flvplayer.swf', 'flashvars', 'file='+serverData, 'quality', 'high', 'wmode', 'transparent', 'allowfullscreen', 'true'));
		jQuery("#linkinput").val(serverData);
	} catch (ex) {
		this.debug(ex);
	}
}

function video_uploadComplete(file) {
	try {
		/*  I want the next upload to continue automatically so I'll call startUpload here */
		if (this.getStats().files_queued > 0) {
			this.startUpload();
		} else {
			progress.setComplete();
			jQuery("#videoinputminor > p").css("display", "block");
			
			jQuery("#videoinputminor").addClass("off");
			jQuery("#videoinputminor > p").html("<a href='javascript:;' onclick='cancelVideoUpload(this);' class='xw1' style='color: #333'>删除视频</a>");
						
			inputMustListener();
			swfu.setButtonDisabled(false);
			filesNum = currentLoadedBytes = 0;
			filesTotalSize = 0;
			uploadCount = 1;
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function video_uploadError(file, errorCode, message) {
	var imageName =  "error.gif";
	var progress;
	try {
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Cancelled");
				//progress.toggleCancel(false);
			}
			catch (ex1) {
				this.debug(ex1);
			}
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Stopped");
				//progress.toggleCancel(true);
			}
			catch (ex2) {
				this.debug(ex2);
			}
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			imageName = "uploadlimit.gif";
			break;
		default:
			alert(message);
			break;
		}


	} catch (ex3) {
		this.debug(ex3);
	}

}

function cancelVideoUpload(source) {
	jQuery("#thumbnails").fadeOut("fast", function() {
		jQuery("#thumbnails").empty();
		jQuery("#thumbnails").css("display", "");
		jQuery("#videoinputminor").removeClass("off");
		jQuery("#videoinputminor > p").text(mifmData["video"].videostr3);
		jQuery("#linkinput").val(mifmData["video"].linkstr);
		inputMustListener();
		swfu.setFileUploadLimit(1);
		
		if(!mifm_videoValidate()) {
			jQuery("#videoinputminor > p").removeClass("mbm");
			jQuery("#thumbnails").css("display", "none");	
		}
	});
}

/***********************************  图片上传处理 结束 ************************************/


/* ******************************************
 *	FileProgress Object
 *	Control object for displaying file info
 * ****************************************** */

function FileProgress(file, targetID) {
	this.fileProgressID = "divFileProgress";

	this.fileProgressWrapper = document.getElementById(this.fileProgressID);
	if (!this.fileProgressWrapper) {
		this.fileProgressWrapper = document.createElement("div");
		this.fileProgressWrapper.className = "progressWrapper";
		this.fileProgressWrapper.id = this.fileProgressID;

		this.fileProgressElement = document.createElement("div");
		this.fileProgressElement.className = "progressContainer";

		var progressCancel = document.createElement("a");
		progressCancel.className = "progressCancel";
		progressCancel.href = "#";
		progressCancel.style.visibility = "hidden";
		progressCancel.appendChild(document.createTextNode(" "));

		var progressBar = document.createElement("div");
		progressBar.className = "progressBarInProgress";

		//this.fileProgressElement.appendChild(progressCancel);
		this.fileProgressElement.appendChild(progressBar);
		
		var progressStatus = document.createElement("p");
		var progressStatusSpan = document.createElement("span");
		progressStatusSpan.id = "uploadCount";
		progressStatusSpan.appendChild(document.createTextNode(uploadCount));
		progressStatus.appendChild(document.createTextNode("开始上传  "));
		progressStatus.appendChild(progressStatusSpan);
		progressStatus.appendChild(document.createTextNode("/" + filesNum));
		
		this.fileProgressWrapper.appendChild(progressStatus);
		this.fileProgressWrapper.appendChild(this.fileProgressElement);

		document.getElementById(targetID).appendChild(this.fileProgressWrapper);
		this.fileProgressWrapper.style.display = "block";

	} else {
		this.fileProgressElement = this.fileProgressWrapper.lastChild;
	}

	this.height = this.fileProgressWrapper.offsetHeight;

}
FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressElement.childNodes[0].className = "progressBarInProgress";
	this.fileProgressElement.childNodes[0].style.width = percentage + "%";
};
FileProgress.prototype.setComplete = function () {
	this.fileProgressElement.childNodes[0].className = "progressBarComplete";
	this.fileProgressElement.childNodes[0].style.width = "";
	this.fileProgressWrapper.parentNode.removeChild(this.fileProgressWrapper);

};
FileProgress.prototype.setError = function () {
	this.fileProgressElement.childNodes[0].className = "progressBarError";
	this.fileProgressElement.childNodes[0].style.width = "";

};
FileProgress.prototype.setCancelled = function () {
	this.fileProgressElement.childNodes[0].className = "progressBarError";
	this.fileProgressElement.childNodes[0].style.width = "";

};
FileProgress.prototype.setStatus = function (status) {
	//this.fileProgressElement.childNodes[2].innerHTML = status;
};

/*FileProgress.prototype.toggleCancel = function (show, swfuploadInstance) {
	this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfuploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressElement.childNodes[0].onclick = function () {
			swfuploadInstance.cancelUpload(fileID);
			return false;
		};
	}
};*/
