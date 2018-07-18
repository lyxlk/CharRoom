var myDialog = {
	alertModal: null,

    confirmModal: null,

    alert: function (content, btnText, fn) {
            var thisObj = this;
            if (!thisObj.alertModal) {
                thisObj.alertModal = $('<div class="maskLayer" id="maskLayer"></div>' +
                    '<div class="mydialog">' +
                    '<div class="close"><a href="javascript:void(0);"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAA/0lEQVRYR+2V4Q3CIBCF2w0coU6gbqCT6wg6gY7gCN5LuARbyr0DTGOCCX8o3vt4x4Nx2Pg3bqw/dIDuQHfgrxw4yp3xkvEm746drJtk3HPrWQdQ6BmKXQgIiF9lAHofwJMcLAD+jJ0cCIhY/BEgVk3wAKDwzYCYi58ttzwA2EUOwi2Ogl6ANQjMa89hu7lz7UkJQAoCczhwLvFSBxQ+bgfm3OItANR2TQkT0a9E1LQg7jmKMhFdxLEEIHXaUdiK6EK8pAW5qDH3RJUDTM7dEJ4W6FVsnfY5xCnpfZhkASZZj8fIEk9FtNljtOlznHOx6hvbgiqRFmegA3QHfubAB1EWSiFDKJTHAAAAAElFTkSuQmCC"/></a></div>' +
                    '<div class="content"></div>' +
                    '<div class="btn"><a href="javascript:void(0);" class="confirm">确定</a></div>' +
                    '</div>');
                $('body').append(thisObj.alertModal);
            }

            var contentHtml = '';
            contentHtml += '' + content + '';
            thisObj.alertModal.find('.content').html(contentHtml);

            if(!btnText) {
                btnText="确定";
            }
            thisObj.alertModal.find('.confirm').html(btnText);
            thisObj.alertModal.show();

            thisObj.alertModal.find('.btn').click(function(){
                thisObj.alertModal.hide();
            	if(fn) {
            		fn();
            	}          	
            });

            thisObj.alertModal.find('.close').click(function(){
                thisObj.alertModal.hide();              
            });
        },

    confirm: function (content, btnText, fn) {
            var thisObj = this;
            if (!thisObj.confirmModal) {
                thisObj.confirmModal = $('<div class="maskLayer" id="maskLayer"></div>' +
                    '<div class="mydialog">' +
                    '<div class="close"><a href="javascript:void(0);"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAA/0lEQVRYR+2V4Q3CIBCF2w0coU6gbqCT6wg6gY7gCN5LuARbyr0DTGOCCX8o3vt4x4Nx2Pg3bqw/dIDuQHfgrxw4yp3xkvEm746drJtk3HPrWQdQ6BmKXQgIiF9lAHofwJMcLAD+jJ0cCIhY/BEgVk3wAKDwzYCYi58ttzwA2EUOwi2Ogl6ANQjMa89hu7lz7UkJQAoCczhwLvFSBxQ+bgfm3OItANR2TQkT0a9E1LQg7jmKMhFdxLEEIHXaUdiK6EK8pAW5qDH3RJUDTM7dEJ4W6FVsnfY5xCnpfZhkASZZj8fIEk9FtNljtOlznHOx6hvbgiqRFmegA3QHfubAB1EWSiFDKJTHAAAAAElFTkSuQmCC"/></a></div>' +
                    '<div class="content"></div>' +
                    '<div class="btn"><a href="javascript:void(0);" class="confirm" data-confirm-ok="1">确定</a><a href="javascript:void(0);" class="cancel" data-confirm-ok="0">取消</a></div>' +
                    '</div>');
                $('body').append(thisObj.confirmModal);
            }

            var contentHtml = '';
            contentHtml += '' + content + '';
            thisObj.confirmModal.find('.content').html(contentHtml);

            if(!btnText) {
                btnText="确定";
            }
            thisObj.confirmModal.find('.confirm').html(btnText);
            thisObj.confirmModal.show();

            thisObj.confirmModal.find('.confirm').click(function(){
                thisObj.confirmModal.hide();
            	if(fn) {
            		fn();
            	}          	
            });

            thisObj.confirmModal.find('.cancel').click(function(){
            	thisObj.confirmModal.hide();          	
            });

            thisObj.confirmModal.find('.close').click(function(){
                thisObj.confirmModal.hide();              
            });
        },
};