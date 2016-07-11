close all;

load('cameraCalibration4.mat');
frame = imread('/Applications/MAMP/htdocs/watss/calibration/images/camera4.jpg');
load('/Users/lorenzocioni/Downloads/K.mat')
load('/Users/lorenzocioni/Downloads/Hi2w.mat')
HW2I=inv(Hi2w');
imgVanline=HW2I*[0 0 1]'; % H=H(N).mat
omega=inv(K')*inv(K); %K'=ptzim(N).K'

imgVinfty=inv(omega)*imgVanline;
imgVinfty=imgVinfty/imgVinfty(3);
imgVinfty(3)=1;

p1 = [502 552 1];
p2 = [425 733 1];
p3 = [283 360 1];
p4 = [174 1026 1];

imshow(frame);
hold on;
plot(p1(2),p1(1),'r.','MarkerSize',30);
plot(p2(2),p2(1),'r.','MarkerSize',30);
plot(p3(2),p3(1),'r.','MarkerSize',30);
plot(p4(2),p4(1),'r.','MarkerSize',30);
comp=0;%size(frame,2);
for mu=-2:0.1:2.0
    W=eye(3)+(1/(1-mu)-1).*((imgVinfty*imgVanline')./(imgVinfty'*imgVanline));
    hold on;
    P1 = W*[p1(1) comp+p1(2) 1]';P1=P1./P1(3);
    plot(comp+P1(2),P1(1),'y.','MarkerSize',30);
    P2 = W*[p2(1) comp+p2(2) 1]';P2=P2./P2(3);
    plot(comp+P2(2),P2(1),'y.','MarkerSize',30);
    P3 = W*[p3(1) comp+p3(2) 1]';P3=P3./P3(3);
    plot(comp+P3(2),P3(1),'y.','MarkerSize',30);
    P4 = W*[p4(1) comp+p4(2) 1]';P4=P4./P4(3);
    plot(comp+P4(2),P4(1),'y.','MarkerSize',30);

    mu

   pause;
end
