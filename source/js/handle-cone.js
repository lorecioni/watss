var cone;
var scene;
var camera;
var renderer;

function addCone(box, id, angle_y, angle_z){
	var angleY = angle_y != null ? angle_y : 0;
	var angleZ = angle_z != null ? angle_z : 0;
	scene = new THREE.Scene();
	camera = new THREE.PerspectiveCamera(75, $(box).width()/$(box).height(), 0.1, 1000);

	renderer = new THREE.WebGLRenderer({alpha: true});
	renderer.setSize($(box).width(), $(box).height());
	//renderer.setSize(150, 150);
	renderer.setClearColor(0x000000, 0)

	$(box).append(renderer.domElement);
	//document.body.appendChild(renderer.domElement);

	var geometry = new THREE.CylinderGeometry(1, 0, 1, 50, 50, false);
	var material = new THREE.MeshNormalMaterial();
	cone = new THREE.Mesh( geometry, material );
	scene.add(cone);

	cone.scale.x = 1; // SCALE
	cone.scale.y = 1; // SCALE
	cone.scale.z = 1; // SCALE

	cone.rotation.z = parseFloat(angleZ+90)*3.14/180;
	cone.rotation.y = parseFloat(angleY+90)*3.14/180;

	camera.position.z = 5;

	var render = function () {
		requestAnimationFrame(render);

		//cone.rotation.x += 0.01;
		//cone.rotation.y += 0.01;
		//cone.rotation.z += 0.01;

		renderer.render(scene, camera);
	};

	render();

	cone_selected = cone;
}

function rotateConeY(id){ //di 5° in 5°
	if(typeof cone != "undefined"){
		cone.rotation.y += 0.087222;
	}	
}
function rotateConeY_less(id){ //di 5° in 5°
	if(typeof cone != "undefined"){
		cone.rotation.y -= 0.087222;
	}	
}

function rotateConeZ(id){ //di 5° in 5°
	if(typeof cone != "undefined"){
		cone.rotation.z += 0.087222;
	}	
}
function rotateConeZ_less(id){ //di 5° in 5°
	if(typeof cone != "undefined"){
		cone.rotation.z -= 0.087222;
	}	
}

function destroyCone(box){
	if(typeof cone != "undefined"){
		scene.remove();
		$(box).children('canvas').remove();
		cone = undefined;
	}
}

function setConeVal(val_y, val_z){
	var angleY = val_y != null ? val_y : 0;
	var angleZ = val_z != null ? val_z : 0;
	if(typeof cone != "undefined"){
		cone.rotation.z = parseFloat(angleZ+90)*3.14/180;
		cone.rotation.y = parseFloat(angleY+90)*3.14/180;
	}
}
