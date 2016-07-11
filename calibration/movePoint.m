function movePoint(~, evnt)
    z = getappdata(0, 'position'); 
    delta = 20;
    switch(evnt.Key)
        case 'rightarrow'
            z(1) = z(1) + delta;
        case 'leftarrow'
            z(1) = z(1) - delta;
        case 'uparrow'
            z(2) = z(2) - delta;
        case 'downarrow'
            z(2) = z(2) + delta;
    end
    disp(z);
    setappdata(0, 'position', z);
end