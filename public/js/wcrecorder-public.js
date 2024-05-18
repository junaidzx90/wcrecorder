class WCAudioRecorder {
	constructor(recorderInstanceId) {
		this.status = false;
		this.audioContext = null;
		this.recorder = null;
		this.gumStream = null;
		this.timerInterval = null;
		this.startTime = 0;
		this.input = null;
		this.timerElement = null;
		this.recordingsListElement = null;
		this.recorderInstance = null;
		this.isPaused = false;
		this.init(recorderInstanceId);
		this.listItems = []
	}

	updateTimer() {
		const elapsedTime = Math.floor((Date.now() - this.startTime) / 1000);
		const minutes = Math.floor(elapsedTime / 60);
		const seconds = elapsedTime % 60;
		const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
		this.timerElement.textContent = formattedTime;
	}

	createAnimation() {
		const wrapper = this.recorderInstance.querySelector(".recording-visual");
		wrapper.innerHTML = '<canvas height="50px"></canvas>';
		const animationCanvas = wrapper.querySelector("canvas");

		const animationCtx = animationCanvas.getContext("2d");
		const analyser = this.audioContext.createAnalyser();
		this.input.connect(analyser);

		analyser.fftSize = 2048;
		const bufferLength = analyser.frequencyBinCount;
		const dataArray = new Uint8Array(bufferLength);
		animationCtx.clearRect(0, 0, animationCanvas.width, animationCanvas.height);
		animationCanvas.height = 50;

		const draw = () => {
			requestAnimationFrame(draw);
			if (!this.isPaused) {
				analyser.getByteTimeDomainData(dataArray);
				animationCtx.clearRect(0, 0, animationCanvas.width, animationCanvas.height);
				animationCtx.fillStyle = 'rgba(255, 255, 255, 0.01)';
				animationCtx.fillRect(0, 0, animationCanvas.width, animationCanvas.height);
				animationCtx.lineWidth = 2;

				const gradient = animationCtx.createLinearGradient(0, 0, animationCanvas.width, 0);
				gradient.addColorStop(0, "#0274e6");
				gradient.addColorStop(1, "#ff5722");
				animationCtx.strokeStyle = gradient;

				animationCtx.beginPath();
				let sliceWidth = animationCanvas.width / bufferLength;
				let x = 0;

				for (let i = 0; i < bufferLength; i++) {
					let v = dataArray[i] / 128.0;
					let y = v * animationCanvas.height / 2;

					if (i === 0) {
						animationCtx.moveTo(x, y);
					} else {
						animationCtx.lineTo(x, y);
					}

					x += sliceWidth;
				}

				animationCtx.lineTo(animationCanvas.width, animationCanvas.height / 2);
				animationCtx.stroke();
			}
		};

		draw();
	}

	startRecording(button, event) {
		event.preventDefault();

		navigator.mediaDevices.getUserMedia({ audio: true, video: false })
			.then((stream) => {
				this.gumStream = stream;
				this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
				this.input = this.audioContext.createMediaStreamSource(stream);
				this.recorder = new Recorder(this.input);

				this.recorder.record();
				this.recorderInstance.querySelector('.recordButton').style.display = 'none';
				this.recorderInstance.querySelector('.useButton').style.display = 'none';
				this.recorderInstance.querySelector('.stopButton').style.display = 'block';
				this.recorderInstance.querySelector('.pauseButton').style.display = 'block';
				console.log('Recording...');

				this.status = true;

				this.startTime = Date.now();
				this.pausedTime = 0; // Reset paused time
				this.isPaused = false;
				this.createAnimation();
				this.timerInterval = setInterval(() => this.updateTimer(), 1000);
			})
			.catch((err) => {
				console.log('The following error occurred: ' + err);
			});
	}

	stopRecording(button, event) {
		event.preventDefault();

		this.recorder && this.recorder.stop();
		this.recorderInstance.querySelector('.recordButton').style.display = 'block';
		this.recorderInstance.querySelector('.useButton').style.display = 'block';
		this.recorderInstance.querySelector('.stopButton').style.display = 'none';
		this.recorderInstance.querySelector('.pauseButton').style.display = 'none';
		this.recorderInstance.querySelector('.useButton').removeAttribute("disabled")
		console.log('Stopped recording.');

		clearInterval(this.timerInterval);
		this.timerElement.textContent = '';
		this.status = false;
		
		this.createDownloadLink();
		this.recorder.clear();
		this.isPaused = false; // Reset pause state
		this.gumStream.getAudioTracks().forEach(track => track.stop());
	}
	
	pauseRecording(button, event) {
		event.preventDefault();

		if (!this.isPaused) {
			this.recorder.stop();
			this.isPaused = true;
			clearInterval(this.timerInterval);
			this.pausedTime += Date.now() - this.startTime; // Add paused time
			button.textContent = 'Resume';
		} else {
			this.recorder.record();
			this.isPaused = false;
			this.startTime = Date.now() - this.pausedTime; // Adjust start time
			this.timerInterval = setInterval(() => this.updateTimer(), 1000);
			button.textContent = 'Pause';
		}
	}

	recordSelect(select){
		const wrapper = this.recorderInstance.querySelector(".recording-visual");
		wrapper.innerHTML = `<audio class="wcsaudio" controls src="${select.value}"></audio>`;

		this.recorderInstance.querySelector('.useButton').style.display = 'block';
		this.recorderInstance.querySelector('.useButton').removeAttribute("disabled")

		if(select.value === ""){
			this.recorderInstance.querySelector('.useButton').style.display = 'none';
			const wrapper = this.recorderInstance.querySelector(".recording-visual");
			wrapper.innerHTML = `<div class="recording-visual"><canvas height="10"></canvas></div>`;
		}
	}

	async uploadInServer(url){
		const response = await fetch(url);
    	const blob = await response.blob();

		const formData = new FormData();
		formData.append('action', 'upload_from_blob');
		formData.append('audio', blob, 'audio_recording.wav');

		document.getElementById('place_order').setAttribute("disabled", "disabled");

		const uploadedUrl = await fetch(wcsobj.ajaxurl, {
			method: 'POST',
			body: formData
		}).then(response => response.json()).then(result => {
			return result;
		}).catch(error => {
			console.error('Error:', error);
		});

		return uploadedUrl;
	}

	savingRecords(url){
		const option = document.createElement('option');
		option.value = url;
		option.textContent = `Recording ${this.recordingsListElement.options.length}`;

		this.listItems.push(option);
		if(this.recordingsListElement.options.length == 0){
			this.recordingsListElement.innerHTML = "<option value=''>Select previous record</option>";
		}
		this.listItems.forEach(opt => {
			this.recordingsListElement.appendChild(opt);
		});

		const recselect = this.recorderInstance.querySelector(".previousRecords");
		recselect.style.display = "block";
	}

	async useRecording(button, event){
		event.preventDefault();
		button.setAttribute("disabled", "disabled");

		let blobUrl = this.recorderInstance.querySelector(".audiolist").value;
		if(blobUrl.includes("blob")){
			blobUrl = await this.uploadInServer(blobUrl);
		}

		if(blobUrl){
			document.getElementById("wcsound_record").value = blobUrl;
			button.style.display = "none";
			document.getElementById('place_order').removeAttribute("disabled");
		}
	}

	createPlayer(blob) {
		const blobUrl = URL.createObjectURL(blob);			
		this.savingRecords(blobUrl); // Saving
			
		const wrapper = this.recorderInstance.querySelector(".recording-visual");
		wrapper.innerHTML = `<audio class="wcsaudio" controls src="${blobUrl}"></audio>`;
		this.recorderInstance.querySelector(".audiolist").value = blobUrl;
	}

	createDownloadLink() {
		this.recorder && this.recorder.exportWAV((blob) => this.createPlayer(blob));
	}

	init(recorderInstanceId) {
		this.recorderInstance = document.getElementById(recorderInstanceId);

		if (this.recorderInstance) {
			const moduleContents = `
				<div class="wcswrapper">
					<div class="previousRecords">
						<h3>Select your existing record</h3>
						<select class="audiolist" onchange="audioRecorder.recordSelect(this);">
							<option value=''>Select previous record</option>
						</select>    
					</div>
					
					<div class="wc_inner">
						<h3>Record your response by clicking on the button below...</h3>
						<div class="recording-visual"><canvas height="10"></canvas></div>
						<div class="metaInfo">
							<div class="wcsRecMeta">
								<div class="wcrecBtns">
									<button class="recordButton wcsbtn" onclick="audioRecorder.startRecording(this, event);">
										<i class="fas fa-microphone"></i>
										<span>Record</span>
									</button>
									<button class="useButton wcsbtn" onclick="audioRecorder.useRecording(this, event);">
										<span>Use</span>
									</button>
									<button class="stopButton wcsbtn" onclick="audioRecorder.stopRecording(this, event);">
										<span>Stop</span>
									</button>
									<button class="pauseButton wcsbtn" onclick="audioRecorder.pauseRecording(this, event);">
										<span>Pause</span>
									</button> 
								</div>
								<div class="wcsTimer">
									<span class="current-timer">00:00</span>
								</div>    
							</div>
						</div>
					</div>
				</div>`;

			this.recorderInstance.innerHTML = moduleContents;
			this.timerElement = this.recorderInstance.querySelector('.current-timer');
			this.recordingsListElement = this.recorderInstance.querySelector('.audiolist');
			const recselect = this.recorderInstance.querySelector(".previousRecords");

			const savedSounds = JSON.parse(wcsobj.sounds);
			if(savedSounds?.length > 0){
				this.recordingsListElement.innerHTML = "<option value=''>Select previous record</option>";
				savedSounds.forEach(data => {
					const option = document.createElement('option');
					option.value = data.value;
					option.textContent = data.text;
					
					this.recordingsListElement.appendChild(option);
				});
				recselect.style.display = "block";
			}
		}
	}
}

let audioRecorder;
window.onload = function init() {
	audioRecorder = new WCAudioRecorder('wcsoundbox');
};