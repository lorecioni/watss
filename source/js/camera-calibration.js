var cameraCalibration = [];

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
				cameraCalibration["rotation"] = math.eval(response.rotation);
				cameraCalibration["translation"] = math.eval(response.translation);
				cameraCalibration["param"] = math.eval(response.param);
				P = math.concat(math.subset(cameraCalibration.rotation, math.index([0, 1, 2],[0,1])), 
						cameraCalibration.translation, 1)
				cameraCalibration['Hw'] = math.multiply(cameraCalibration.intrinsic, P);
				cameraCalibration['HI2W'] = math.inv(cameraCalibration.Hw);
				cameraCalibration['HW2I'] = math.transpose(math.inv(cameraCalibration.HI2W));
				
				cameraCalibration['l'] = computeInfiniteLine();
				cameraCalibration['v'] = computeV();
				cameraCalibration['W'] = computeW();
				
				console.log(cameraCalibration)
			} else {
				console.log('Camera calibration not set');
			}
		}
	});
}


function computeInfiniteLine(){
	return math.multiply(cameraCalibration.HW2I, math.eval('[0; 0; 1]'));
}

function computeV(){
	var t = math.multiply(cameraCalibration.intrinsic, math.transpose(cameraCalibration.intrinsic));
	return math.multiply(t, cameraCalibration.l);
}

function computeW(){
	var num = math.multiply(cameraCalibration.v, math.transpose(cameraCalibration.l));
	var den = math.multiply(math.transpose(cameraCalibration.v), cameraCalibration.l);
	den = math.subset(den, math.index(0, 0));
	var I = math.eye(3);	
	return math.add(I, math.multiply((cameraCalibration.param - 1), math.dotDivide(num, den)))
}