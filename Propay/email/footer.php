<?php
function email_footer() {
$year = date('Y');
	
ob_start();
?>

															</div>
														</td>
													</tr>
												</table>
												<!-- End Content -->
											</td>
										</tr>
									</table>
									<!-- End Body -->
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
										<tr>
											<td valign="top" style='background-color:#fdfdfd'>
												<table border="0" cellpadding="10" cellspacing="0" width="100%">
													<tr>
														<td colspan="2" valign="middle" id="credit" style='padding:0 48px 48px 48px;border:0;color:#38474f;font-family:Arial;font-size:12px;line-height:125%;text-align:center'>
															<p>Myclientpay © <?php echo $year; ?></p>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>

<?php
$message = ob_get_clean();

return $message;

}
?>
