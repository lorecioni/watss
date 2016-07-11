close all;
load('cameraCalibration1.mat');
frame = imread('/Applications/MAMP/htdocs/watss/source/frames/1/16h22m17s_3944.jpg');

%mu = 0.5;
mu = -0.65;

imgVanline=HW2I'*[0 0 1]'; % H=H(N).mat
omega=inv(K')*inv(K); %K'=ptzim(N).K'

imgVinfty=inv(omega)*imgVanline;
imgVinfty=imgVinfty/imgVinfty(3);
imgVinfty(3)=1;

W=eye(3)+(1/(1-mu)-1).*((imgVinfty*imgVanline')./(imgVinfty'*imgVanline));

%W = I + (1/(1 - mu) - 1)*((v * l') ./ (v' * l));

z = [768.989874403743 205.055561315589 1];
%P = z * HW2I;
%P = P./P(3)


setappdata(0, 'position', z);

f = figure;
set(f,'KeyPressFcn', @movePoint);

while(true)
   z = getappdata(0, 'position'); 
   
   t = [z(2) z(1) 1];
   
   Z = W * [t(1) size(frame,2)-t(2) 1]';
   Z = Z ./ Z(3);
   
   Z = [size(frame,2)-Z(2) Z(1) Z(3)];

   h = abs(z(2) - Z(2));
   w = h/2;
   
   %I = insertShape(frame, 'rectangle', [z(1) abs(size(I, 1) -z(2)) w h], 'LineWidth', 3);
   I = insertShape(frame, 'rectangle', [z(1) (z(2) - h) w h], 'LineWidth', 3);
   imshow(I);
end

