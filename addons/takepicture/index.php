<video id="player" controls playsinline autoplay></video><br>
<button id="capture">Chụp hình</button> <button onclick="closeCapture()">Đóng</button><br>
<canvas id="canvas" width="320" height="240"></canvas>
<script>
  const player = document.getElementById('player');
  const canvas = document.getElementById('canvas');
  const context = canvas.getContext('2d');
  const captureButton = document.getElementById('capture');

  const constraints = {
    video: true,
  };
function closeCapture()
  {
	 
	  player.pause();
	   closePopup();
  }
  captureButton.addEventListener('click', () => {
    
    context.drawImage(player, 0, 0, canvas.width, canvas.height);
	closeCapture();
	const base64Canvas = canvas.toDataURL("image/jpeg").split(';base64,')[1];
	alert(base64Canvas);
	
	
  });
  navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
    player.srcObject = stream;
  });
  
</script>