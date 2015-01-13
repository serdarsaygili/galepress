SELECT 
	`CustomerNo`, `CustomerName`, `ApplicationName`, `ContentName`, COUNT(*) AS `DownloadCount`
FROM (
	SELECT 
		cu.`CustomerID`, cu.`CustomerNo`, cu.`CustomerName`, 
		ap.`ApplicationID`, ap.`Name` AS `ApplicationName`, ap.`ExpirationDate`, 
		cn.`ContentID`, cn.`Name` AS `ContentName`, IFNULL(cn.`Approval`, 0) AS `ContentApproval`, IFNULL(cn.`Blocked`, 0) AS `ContentBlocked`, cn.`TotalFileSize` AS `AmountOfFileSize`,
		rq.`DataTransferred`
	FROM `Customer` cu 
		INNER JOIN `Application` ap ON ap.`CustomerID`=cu.`CustomerID` AND ap.`StatusID`=1 
		INNER JOIN `Content` cn ON cn.`ApplicationID`=ap.`ApplicationID` AND cn.`StatusID`=1 
		INNER JOIN `Request` rq ON rq.`ContentID`=cn.`ContentID` AND rq.`RequestTypeID`=1001 AND rq.`RequestDate` BETWEEN '{SD}' AND '{ED}' AND rq.`Percentage`=100
	WHERE 
		cu.`CustomerID`=COALESCE({CUSTOMERID}, cu.`CustomerID`) AND 
		ap.`ApplicationID`=COALESCE({APPLICATIONID}, ap.`ApplicationID`) AND 
		cn.`ContentID`=COALESCE({CONTENTID}, cn.`ContentID`) AND 
		cu.`StatusID`=1
) t 
GROUP BY `CustomerNo`, `CustomerName`, `ApplicationName`, `ContentName`