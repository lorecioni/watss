% Camera matrix
close all;

load('cameraCalibration3.mat');
frame = imread('/Applications/MAMP/htdocs/watss/calibration/images/camera3.jpg');
load('/Users/lorenzocioni/Downloads/Hi2w-1.mat')
HW2I = inv(Hi2w');
imgVanline=HW2I*[0 0 1]'; % H=H(N).mat
omega=inv(K')*inv(K); %K'=ptzim(N).K'

imgVinfty=inv(omega)*imgVanline;
imgVinfty=imgVinfty/imgVinfty(3);
imgVinfty(3)=1;


p1 = [360 39 1];
p2 = [250 333 1];
p3 = [230 1027 1];

imshow(frame);
hold on;
plot(p1(2),p1(1),'r.','MarkerSize',30);
plot(p2(2),p2(1),'r.','MarkerSize',30);
plot(p3(2),p3(1),'r.','MarkerSize',30);

for mu=0:-0.01:-2.0
    W=eye(3)+(1/(1-mu)-1).*((imgVinfty*imgVanline')./(imgVinfty'*imgVanline));
    hold on;
    P1 = W*[p1(1) p1(2) 1]';P1=P1./P1(3);
    plot(P1(2),P1(1),'y.','MarkerSize',30);
    P2 = W*[p2(1) p2(2) 1]';P2=P2./P2(3);
    plot(P2(2),P2(1),'y.','MarkerSize',30);
    P3 = W*[p3(1) p3(2) 1]';P3=P3./P3(3);
    plot(P3(2),P3(1),'y.','MarkerSize',30);

    mu

   pause;
end
