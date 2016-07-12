var cameraCalibration = {};

/**
 * Loads camera calibration for the selected camera in session
 */
function loadCameraCalibration(){
	//Loading camera calibration
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"get-calibration"},
		success: function(response){
			console.log('Loading camera calibration for the selected camera')
			cameraCalibration["active"] = response.calibration;
			if(cameraCalibration.active){
				cameraCalibration["intrinsic"] = math.eval(response.intrinsic);
				cameraCalibration["omography"] = math.eval(response.omography);
				cameraCalibration["param"] = math.eval(response.param);
				cameraCalibration['l'] = computeVanishingLine();
				cameraCalibration['v'] = computeVanishingRect();
				cameraCalibration['W'] = computeW();
				console.log('Camera calibration loaded')				
			} else {
				console.log('Camera calibration not set');
			}
		}
	});
}

/**
 * Check if camera calibration is active
 */
function isCameraCalibrationActive(){
	return cameraCalibration.active;
}

/**
 * Evaluate approximate height of a person given head position
 * @param x
 * @param y
 * @returns
 */
function evaluateApproximateHeight(x, y){
	/** MATLAB script
	 * Feet = [x, y, 1]
	 * Head = inv(W) * Feet'
	 * Head = Head ./ Head(3)
	 */
	if(isCameraCalibrationActive()){
		var feet = math.eval('[' + x + '; ' + y + '; 1]');
		var head = math.multiply(math.inv(cameraCalibration.W), feet);
		head = math.dotDivide(head, math.subset(head, math.index(2, 0)));
		var height = Math.abs(math.subset(head, math.index(1, 0)) - y);
		return height;	
	} else {
		return null;
	}
}

/**
 * Evaluating vanishing lines and W matrix
 */
function computeVanishingLine(){
	/** MATLAB script
	 * l = HW2I * [0 0 1]';
	 */
	return math.multiply(cameraCalibration.omography, math.eval('[0; 0; 1]'));
}

function computeVanishingRect(){
	/** MATLAB script
	omega = inv(K') * inv(K);
	v = inv(omega) * l;
	v = v/v(3);
	*/
	var omega = math.multiply(math.inv(math.transpose(cameraCalibration.intrinsic)), math.inv(cameraCalibration.intrinsic));
	var v = math.multiply(math.inv(omega), cameraCalibration.l);
	return math.dotDivide(v, math.subset(v, math.index(2, 0)));
	
}

function computeW(){
	/**
	 * MATLAB Script
	 * W = eye(3) + (1/(1-mu)-1) .* ((v * l')./(v' * l));
	 */
	var num = math.multiply(cameraCalibration.v, math.transpose(cameraCalibration.l));
	var den = math.multiply(math.transpose(cameraCalibration.v), cameraCalibration.l);
	den = math.subset(den, math.index(0, 0));
	var I = math.eye(3);	
	return math.add(I, math.multiply((1/(1-cameraCalibration.param) - 1), math.dotDivide(num, den)))
}